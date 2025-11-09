<?php

namespace App\Classes\Auth;

use LogicException;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use App\Models\LoginToken as LoginTokenModel;

class LoginToken
{
    public function __construct(
        protected string $app,
        public ?LoginTokenModel $token = null,
        protected ?Carbon $time = null,
    ) {
        $this->time = $this->time ?? Carbon::now();

        if ($token === null) {
            $this->token = new LoginTokenModel();
            $this->token->fill([
                'app' => $app,
                'login_token' => $this->loginTokenString(),
                'issued_at' => $this->setIssuedAt($this->time),
                'valid_until' => $this->setValidUntil($this->time)

            ]);
            $this->token->save();
        }
    }

    protected function loginTokenString(): string
    {
        do {
            $tokenString = Str::uuid()->toString(); // or Str::random(32)
        } while (LoginTokenModel::withTrashed()->where('login_token', $tokenString)->exists());
        return $tokenString;
    }
    
    protected function setIssuedAt(?Carbon $timeOfIssue = null): Carbon
    {
        // $this->token->issued_at = $issued_at ?? Carbon::now();
        return $timeOfIssue ?? Carbon::now();
    }

    protected function setValidUntil(Carbon $timeOfIssue): ?Carbon
    {
        return $timeOfIssue->copy()->addMinutes(5);
    }

    public function isValid(?Carbon $time = null): bool
    {
        $time = $time ?? Carbon::now();
        return $time->lte($this->token->valid_until);
    }

    public function use(): bool
    {
        $this->token->delete();
        return $this->isValid() ? true : false;
    }

    public function isUsed(int|string $token): bool
    {
        $query = LoginTokenModel::withTrashed();

        if (is_int($token)) {
            $query->where('id', $token);
        } else {
            $query->where('login_token', $token);
        }

        $loginToken = $query->first();

        return $loginToken?->trashed() ?? false;
    }

}