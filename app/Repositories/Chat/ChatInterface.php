<?php

namespace App\Repositories\Chat;

interface ChatInterface
{
    public function send($options);
    public function getMessage($chat_id, $page);
    public function getListChat($page);
    public function getListIdChat($user_id);
    public function getById($id);
    public function getByUserId($user_id);
}