<?php

namespace App\Http\Traits;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

trait HasProfileImage
{
    /**
     * @param \Illuminate\Http\Request $request
     * @param string|mixed $requestAttributeName
     * @return mixed
     */
    public function uploadProfileImageFromRequest(Request $request, $requestAttributeName = 'file')
    {
        if (!is_null($this->profile_picture)) {
            Storage::disk('public')->delete($this->profile_picture_path);
        }

        $path = $request->file($requestAttributeName)
            ->storeAs(
                'avatars',
                Str::random(30) . '.' . $request->file->extension(),
                'public'
            );

        $this->update(['profile_picture_path' => $path]);

        return $this->fresh()->avatar_url;
    }

    public function deleteProfileImage(): bool
    {
        Storage::disk('public')->delete($this->profile_picture_path);

        return $this->update(['profile_picture_path' => null]);
    }

    public function getAvatarUrlAttribute(): ?string
    {
        return $this->profile_picture_path ? Storage::disk('public')->url($this->profile_picture_path) : null;
    }

    /**
     * get Avatar Url Or Blank Attribute
     * TODO: find usages or remove
     */
    public function getAvatarUrlOrBlankAttribute(): string
    {
        return $this?->avatar_url ?? asset('images/blank.png');
    }
}
