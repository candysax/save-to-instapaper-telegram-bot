<?php

namespace SaveToInstapaperBot\Adapters;

use GuzzleHttp\Client;

class TelegraphAdapter
{
    public static function createAccount(string $tgUserName)
    {
        $client = new Client();

        return $client->request('POST', $_ENV['CREATE_ACC_TGH_URL'], [
            'form_params' => [
                'short_name' => $tgUserName,
                'author_name' => $tgUserName,
            ],
        ]);
    }

    public static function createPage(
        string $title,
        string $accessToken,
        string $content
    ) {
        $client = new Client();

        return $client->request('POST', $_ENV['CREATE_PAGE_TGH_URL'], [
            'form_params' => [
                'access_token' => $accessToken,
                'title' => $title,
                'content' => $content,
                'return_content' => true,
            ],
        ]);
    }
}
