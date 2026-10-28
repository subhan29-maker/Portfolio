<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateProfileRequest;
use App\Models\Setting;
use App\Services\ProfileImageService;
use App\Support\PortfolioSettings;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/**
 * Profile & settings: the picture shown on the public pages plus the
 * portfolio details (name, title, introduction, contact and social links).
 */
class ProfileController extends Controller
{
    public function __construct(
        private PortfolioSettings $settings,
        private ProfileImageService $images,
    ) {}

    public function edit(): View
    {
        $picture = $this->settings->profileImage();

        return view('admin.profile.edit', [
            'values' => $this->settings->formValues(),
            'picture' => $picture,
            'picturePath' => Setting::get(PortfolioSettings::PROFILE_IMAGE_PATH),
            'pictureAlt' => (string) ($picture['photo_alt'] ?? config('portfolio.profile.photo_alt')),
            'defaultPhoto' => (string) config('portfolio.profile.photo'),
        ]);
    }

    /**
     * Save the picture (when a new one was uploaded) and the details.
     *
     * Order matters: the new picture is written to storage first, so a failed
     * upload leaves the current picture untouched; the previous file is only
     * removed once the settings point at the new one.
     */
    public function update(UpdateProfileRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $previousPath = Setting::get(PortfolioSettings::PROFILE_IMAGE_PATH);
        $newPath = null;

        if ($request->hasFile('photo')) {
            $newPath = $this->images->store($request->file('photo'));
        }

        // Details are saved even when the upload failed, and vice versa.
        $this->settings->setDetails($data);

        if ($newPath !== null) {
            $this->settings->setProfileImage($newPath, (string) ($data['photo_alt'] ?? ''));
        } elseif ($request->filled('photo_alt')) {
            $this->settings->setProfileImage(
                (string) $previousPath,
                (string) $data['photo_alt'],
            );
        }

        if ($newPath !== null && filled($previousPath) && $previousPath !== $newPath) {
            $this->images->delete($previousPath);
        }

        return redirect()
            ->route('admin.profile.edit')
            ->with('status', 'Your portfolio profile was updated.');
    }

    /**
     * Reset to the default picture, with a confirmation in the interface.
     *
     * The setting is cleared before the file is deleted, so the site can
     * never keep referencing a picture that has already been removed.
     */
    public function destroyPicture(): RedirectResponse
    {
        $path = Setting::get(PortfolioSettings::PROFILE_IMAGE_PATH);

        $this->settings->clearProfileImage();
        $this->images->delete($path);

        return back()->with('status', 'The default profile picture is in use again.');
    }
}
