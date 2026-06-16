<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Dealership;
use App\Models\Email;
use App\Models\EmailAttachment;
use App\Models\User;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class EmailAttachmentDownloadController extends Controller
{
    public function __invoke(EmailAttachment $attachment): StreamedResponse|Response
    {
        $user = Auth::user();

        abort_unless($user instanceof User, 403);

        $email = $attachment->email;

        abort_unless($email instanceof Email, 404);

        $dealership = $email->dealership;

        abort_unless($dealership instanceof Dealership, 404);

        if (! $user->isGm()) {
            abort_unless(
                $user->dealerships()->whereKey($dealership->id)->exists(),
                403,
            );
        }

        abort_unless(Storage::disk('local')->exists($attachment->path), 404);

        return Storage::disk('local')->download(
            $attachment->path,
            $attachment->original_name,
        );
    }
}
