<?php

namespace App\Interfaces;

interface NotifishInterface
{
   public function login($instance_uuid);
   public function getLoginStatus($instance_uuid);
   public function sendMessage($instance_uuid, $message, $phone, $identifier = null);
   public function sendArchive($instance_uuid, $archive_path, $phone, $identifier = null, $mediaType = null, $filename = null, $caption = '');
   public function logout($instance_uuid);
   public function restart($instance_uuid);
   public function sendMessageBeforeBoleto($boleto_id);
   public function sendMessageBeforePix($pix_id);
   public function sendNotificationMessage($message);
   public function notifySendBoletoWhatsApp($ids, $success, $pix = false);
   public function notifySendNfeWhatsApp($ids, $success);
}
