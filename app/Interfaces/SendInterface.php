<?php

namespace App\Interfaces;

interface SendInterface
{
    public function sendEmailPix($id);
    public function sendNotificationSucessOrFailed($model, $title, $view, $success_id);
}