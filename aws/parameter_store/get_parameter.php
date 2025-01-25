<?php

$profile = "your profile";

// aws ssmを連続で呼び出しすぎるとAWS側で怒られて一時的に値が取得できなくなるので、要調整

// 該当環境の名前を一括取得する
// $ aws ssm describe-parameters --query "Parameters[*].Name" --profile "your profile"

// パラメータ名一覧
$parameterNames = [];

// パラメータ取得コマンド
// $ aws ssm get-parameter --name "target_parameter_name"

// 説明取得コマンド
// aws ssm describe-parameters --query "Parameters[?Name=='/hoge/fuga'].Description" --profile "your profile"

// 名前でループしてjsonファイル形式に整える
$jsonArrays = [];
foreach($parameterNames as  $n => $parameterName) {
    // 該当環境からパラメータの取得
    $output = null;
    $resultCode = null;   
    exec("aws ssm get-parameter --name " . $parameterName . " --profile $profile", $output, $resultCode);

    // 改行毎に配列になっているのでjson形式になるよう結合する
    $strings = "";
    $strings = implode("", $output);
    // 配列に変換
    $tmp = json_decode($strings, true);

    // descriptionはget-parameterで取得できないので、別途取得する
    $output = null;
    $resultCode = null;
    $isSuccess = exec("aws ssm describe-parameters --query \"Parameters[?Name=='$parameterName'].Description\" --profile $profile", $output, $resultCode);
    
    // descriptionが存在する場合には値を配列にセットする
    $stringDescriptions = implode("", $output);
    if ($isSuccess && count(json_decode($stringDescriptions, true)) > 0 ) {
        $tmp["Parameter"]["Description"] = json_decode($stringDescriptions, true)[0];
    }

    $jsonArrays[] = $tmp;

    if($n == 30) {
        sleep(10);
    }
}

$outArr = [];
foreach($jsonArrays as $index => $arr) {
    $outArr[$index]["Name"] = $arr["Parameter"]["Name"];
    if(isset($arr["Parameter"]["Description"])) {
        $outArr[$index]["Description"] = $arr["Parameter"]["Description"];
    }
}

// ファイルに書き出し
file_put_contents('./getParameter.json', json_encode($outArr, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));