<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AccountWelcomeMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $user,
        public ?User $createdBy = null,
        public string $setupUrl,
        public int $expiresInMinutes
    ) {}

    public function build(): self
    {
        return $this
            ->subject('Bun venit in UniProjectManager')
            ->view('emails.account-welcome')
            ->with([
                'user' => $this->user,
                'createdBy' => $this->createdBy,
                'setupUrl' => $this->setupUrl,
                'expiresInMinutes' => $this->expiresInMinutes,
            ]);
    }
}
