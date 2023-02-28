<?php

namespace TaFarda\IAuth\app\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Http;
use TaFarda\IAuth\Models\Product;
use Illuminate\Bus\Queueable;

class UserVerification extends Notification implements ShouldQueue
{
    use Queueable;

    protected Product $product;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Product $product)
    {
        $this->product = $product;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function via(mixed $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(mixed $notifiable)
    {
        if (config('app.env') == 'production') {
            if ($notifiable->mobile) {
                $apiKey = config('tafarda_iauth.api_key');
                Http::get('https://api.kavenegar.com/v1/' . $apiKey . '/verify/lookup.json', [
                    'receptor' => $notifiable->mobile,
                    'token' => $notifiable->otp_code,
                    'template' => $this->product->sms_verify_template
                ]);
            }
            if ($notifiable->email) {
                return (new MailMessage)
                    ->line(trans('Your validation code to enter the system.') . $this->product->title)
                    ->line($notifiable->otp_code);
            }
        }
        return true;
    }

    /**
     * Get the array representation of the notification.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function toArray(mixed $notifiable): array
    {
        return [
            //
        ];
    }


}
