<?php

function apiAuthResponse($message, $token, $data = null, $status = 200)
{
    return response()->json([
        'data' => [
            'user' => $data,
            'token' => $token,
        ],
        'message' => $message,
        'status' => $status,
    ]);
}

function apiResponse($message, $data = null, $status = 200)
{
    return response()->json([
        'data' => $data,
        'message' => $message,
        'status' => $status,
    ]);
}

if (!function_exists('calculateWithdrawalThreshold')) {
    function calculateWithdrawalThreshold($investment)
    {

        $profitPercentage = $investment->profit_percentage;

        $withdrawalThreshold = $profitPercentage * 0.1; // 10% of the profit percentage

        return $withdrawalThreshold;
    }
}
