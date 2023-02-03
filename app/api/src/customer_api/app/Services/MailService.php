<?php


namespace App\Services;
use App\Services\Service as BaseService;
use Illuminate\Support\Facades\Log;
use App\Services\Interfaces\MailServiceInterface;
use Illuminate\Support\Facades\Mail;
use App\Mail\SendMailForm;
class MailService extends BaseService implements MailServiceInterface
{
    /**
     * send mail form
     * @param $request
     * @return bool
     */
    public function sendMailForm($request){
        try {
            $email = new SendMailForm(
                $request->subject,
                $request->text,
            );

            Mail::send($email);

            Log::debug("Send a mail form");
            return true;
        } catch (\Exception $exception) {
            Log::error( $exception->getMessage() . '----Line----' . $exception->getLine());
            return false;
        }
    }
}
