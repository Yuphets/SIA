<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class BudgetAlertMail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $percentage;
    public $total;
    public $limit;

    public function __construct($user, $percentage, $total, $limit)
    {
        $this->user = $user;
        $this->percentage = $percentage;
        $this->total = $total;
        $this->limit = $limit;
    }

    public function build()
    {
        $subject = $this->percentage >= 100 ? 'Budget Exceeded!' : 'Budget Warning!';
        return $this->subject($subject)
                    ->view('emails.budget-alert');
    }
}
