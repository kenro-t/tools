<?php

// jsonデータの取得
$inputJson = file_get_contents("./hoge.json");
$parameters = json_decode($inputJson, true);

foreach($parameters as $parameter) {
    // 変数に代入
    $name = $parameter["Parameter"]["Name"];
    $type = $parameter["Parameter"]["Type"];
    $value = $parameter["Parameter"]["Value"];
    // $version = $parameter["Parameter"]["Version"];
    // $lastModifiedDate = $parameter["Parameter"]["LastModifiedDate"];
    // $arn = $parameter["Parameter"]["ARN"];
    // $dataType = $parameter["Parameter"]["DataType"];


    // setParameterコマンドの成形
    $parameterSetCommand = "aws ssm put-parameter --name \"$name\" ";
    $parameterSetCommand .= "--value \"$value\" ";
    $parameterSetCommand .= "--type \"$type\" ";
    $parameterSetCommand .= "--overwrite ";

    // 説明が存在する場合にはdescriptionオプションを追加
    if(isset($parameter["Parameter"]["Description"])) {
        $parameterSetCommand .= "--description \"" . $parameter["Parameter"]["Description"] . "\" ";
    }
    
    $parameterSetCommand .= "--profile your profile";

    // debug
    // echo $parameterSetCommand;
    // echo "\n";
    // die;

    $output = null;
    $resultCode = null;
    if (!exec($parameterSetCommand, $output, $resultCode)) {
        echo "処理に失敗した\n";
        echo "$parameterSetCommand\n";
        echo "$resultCode\n";
        print_r($output);
        echo "\n";
        die;
    }
}
