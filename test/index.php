<?php

require dirname(__DIR__) . "/src/JWTCodec.php";

$jwt_class = new JWTCodec();

$token = $jwt_class->encode(
    [
        'sub' => 1,
        'name' => "Wil Gonzales",
        'api_key' => "asdasdasjdnj2n13j23njasnfdajklsndaskjdn"
    ]
);



$shesh = $jwt_class->decode("eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJzdWIiOjU2LCJuYW1lIjoiV2lsIEdvbnphbGVzIiwiYXBpX2tleSI6IjQ3ZGY3NDVhNDQ2NTBlMGQ2MTU4NmNmZGUzOWNlMjFiIn0.I6yKYoqyEGKd9Y-lYKemRduw5opAGrGYh1foLLvEuy8");

var_dump($shesh);
