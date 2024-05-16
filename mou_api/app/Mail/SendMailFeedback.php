<?php

namespace App\Mail;

use App\Feedback;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SendMailFeedback extends Mailable
{
    use Queueable, SerializesModels;

    protected $feedback;

    /**
     * Create a new message instance.
     */
    public function __construct(Feedback $feedback)
    {
        //
        $this->feedback = $feedback;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $link = url('/');
        $feedback = $this->feedback;
        $this->feedback->load('user');

        return $this->subject(config('app.name').' - Feedback')->markdown('mail.event.feedback', compact('link', 'feedback'));
    }
}
