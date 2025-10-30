<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Helpers\CmsHelper;
use App\Helpers\StringHelper;
use App\Helpers\ValidateHelper;
use App\Helpers\MailHelper;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\Models\User;
use Sqids\Sqids;

class AuthController extends Controller
{
    /**
     * View for install page
     */
    public function install()
    {
        // Prevent reinstallation
        if (User::count() > 0 || file_exists(storage_path('installed.lock'))) {
            abort(403);
        }

        view()->share('pageTitle', __('admin::auth.install.pageTitle'));

        return view('admin::auth.install');
    }

    /**
     * Handle install request
     */
    public function installRequest()
    {
        $name = StringHelper::removeSpaces(request()->get('name'));
        $email = StringHelper::removeSpaces(request()->get('email'));
        $password = request()->get('password');
        $csrf = request()->get('csrf');

        // CSRF block
        if ($csrf) {
            return response()->json([
                'success' => false,
                'message' => __('admin::app.errors.default')
            ]);
        }

        $validate = ValidateHelper::userName($name);
        if ($validate !== true) {
            return response()->json([
                'success' => false,
                'message' => __('admin::auth.install.validate.' . $validate)
            ]);
        }

        $validate = ValidateHelper::userEmail($email);
        if ($validate !== true) {
            return response()->json([
                'success' => false,
                'message' => __('admin::auth.install.validate.' . $validate)
            ]);
        }

        $validate = ValidateHelper::userPassword($password);
        if ($validate !== true) {
            return response()->json([
                'success' => false,
                'message' => __('admin::auth.install.validate.' . $validate)
            ]);
        }

        User::create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($password),
            'role' => 'developer',
            'language' => app()->getLocale(),
        ]);

        // Mark install as complete
        file_put_contents(storage_path('installed.lock'), now());

        session()->flash('install-success', __('admin::auth.install.successText'));

        // Redirect to login
        return response()->json([
            'success' => true,
            'redirect' => route('admin.login')
        ]);
    }

    /**
     * View for login page
     */
    public function login()
    {
        if (Auth::check()) {
            return redirect(route('admin.dashboard'));
        }

        view()->share('pageTitle', __('admin::auth.login.pageTitle'));

        return view('admin::auth.login');
    }

    /**
     * Handle the login request
     */
    public function loginRequest()
    {
        $email = request()->get('email');
        $password = request()->get('password');
        $csrf = request()->get('csrf');

        // CSRF block
        if ($csrf) {
            return response()->json([
                'success' => false,
                'message' => __('admin::app.errors.default')
            ]);
        }

        // Handle sign up
        $response = $this->handleLogin([
            'email' => $email,
            'password' => $password
        ]);

        return response()->json($response);
    }

    /**
     * Handle the login
     */
    private function handleLogin($data)
    {
        $email = !empty($data['email']) ? $data['email'] : '';
        $password = !empty($data['password']) ? $data['password'] : '';

        if (
            Auth::attempt([
                'email' => $email,
                'password' => $password,
                'active' => 1,
            ], true) // TODO add remember checkbox
        ) {
            return [
                'success' => true,
            ];
        }

        return [
            'success' => false,
            'message' => __('admin::auth.login.form.error'),
        ];
    }

    /**
     * Delete account request
     */
    public function deleteAccount()
    {
        // Abort if not logged in
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
            ]);
        }

        $password = request()->get('password');

        // Abort if password is wrong
        if (!Hash::check($password, Auth::user()->password)) {
            return response()->json([
                'success' => false,
                'message' => __('admin::auth.delete.errorWrongPassword')
            ]);
        }

        // Delete account
        $accountDeleted = CmsHelper::deleteUserAccount(Auth::id());

        // Abort if not deleted
        if (!$accountDeleted) {
            return response()->json([
                'success' => false,
            ]);
        }

        // Return success
        return response()->json([
            'success' => true,
            'redirect' => route('admin.login'),
        ]);
    }

    /**
     * View for reset password page
     */
    public function resetPassword()
    {
        view()->share('pageTitle', __('admin::auth.resetPassword.pageTitle'));

        return view('admin::auth.reset-password');
    }

    /**
     * Reset password request
     */
    public function resetPasswordRequest()
    {
        $email = request()->get('email');
        $csrf = request()->get('csrf');

        // CSRF block
        if ($csrf) {
            return response()->json([
                'success' => false,
                'message' => __('admin::app.errors.default')
            ]);
        }

        // Validate email exists
        $user = User::where([
            'email' => $email
        ])->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => __('admin::auth.resetPassword.form.errorEmailText')
            ]);
        }

        // Set reset hash
        $resetPasswordHash = $user->password_reset_hash ? $user->password_reset_hash : Str::random(8);
        $user->password_reset_hash = $resetPasswordHash;
        $user->save();

        // Send mail
        MailHelper::resetPassword($user);

        return response()->json([
            'success' => true,
            'message' => __('admin::auth.resetPassword.form.successText')
        ]);
    }

    /**
     * View for setting new password page
     */
    public function newPassword($userId, $resetPasswordHash)
    {
        $csrf = request()->get('csrf');

        // CSRF block
        if ($csrf) {
            return response()->json([
                'success' => false,
                'message' => __('admin::app.errors.default')
            ]);
        }

        $sqids = new Sqids(config('cms.sqids_salt'));

        $userIdDecoded = $sqids->decode($userId);
        $userIdDecoded = empty($userIdDecoded) ? null : $userIdDecoded[0];

        $user = User::where([
            'id' => $userIdDecoded,
            'password_reset_hash' => $resetPasswordHash
        ])->first();

        // No user found, show error
        if (!$user) {
            session()->flash('new-password-link-expired', __('admin::auth.newPassword.flashMessageErrorResetLinkExpired'));
            return redirect(route('admin.reset-password'));
        }

        view()->share('userId', $userId);
        view()->share('resetPasswordHash', $user->password_reset_hash);

        view()->share('pageTitle', __('admin::auth.newPassword.pageTitle'));

        return view('admin::auth.new-password');
    }

    /**
     * New password request
     */
    public function newPasswordRequest()
    {
        $sqids = new Sqids(config('cms.sqids_salt'));

        $userId = request()->get('userId');
        $userIdDecoded = $sqids->decode($userId);
        $userIdDecoded = empty($userIdDecoded) ? null : $userIdDecoded[0];

        $resetPasswordHash = request()->get('resetPasswordHash');
        $csrf = request()->get('csrf');

        // CSRF block
        if ($csrf) {
            return response()->json([
                'success' => false,
                'message' => __('admin::app.errors.default')
            ]);
        }

        // Check for user
        $user = User::where([
            'id' => $userIdDecoded,
            'password_reset_hash' => $resetPasswordHash
        ])->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => __('admin::auth.newPassword.validate.errorExpired')
            ]);
        }

        $password = request()->get('password');
        $passwordRepeat = request()->get('passwordRepeat');

        // Validate password
        if (strlen($password) < 8) {
            return response()->json([
                'success' => false,
                'message' => __('admin::auth.newPassword.validate.errorPasswordMin')
            ]);
        }
        if (strlen($password) > 50) {
            return response()->json([
                'success' => false,
                'message' => __('admin::auth.newPassword.validate.errorPasswordMax')
            ]);
        }
        if ($password != $passwordRepeat) {
            return response()->json([
                'success' => false,
                'message' => __('admin::auth.newPassword.validate.errorPasswordMatch')
            ]);
        }

        // Save new password
        $user->password = Hash::make($password);
        $user->password_changed_at = new \DateTime();
        $user->password_reset_hash = null;
        $user->save();

        return response()->json([
            'success' => true,
            'message' => __('admin::auth.newPassword.form.successText')
        ]);
    }

    /**
     * Handle verify email
     */
    // public function verifyEmail($userId, $email, $emailVerifyHash)
    // {
    //     $sqids = new Sqids(config('app.sqids_salt'));

    //     $userId = $sqids->decode($userId);
    //     $userId = empty($userId) ? null : $userId[0];

    //     $email = StringHelper::decrypt($email);

    //     // Get user
    //     $user = User::where([
    //         'id' => $userId,
    //         'email' => $email ? $email : null,
    //         'email_verify_hash' => $emailVerifyHash
    //     ])->first();

    //     // No user found, error
    //     if (!$user) {
    //         session()->flash('message', [
    //             'color' => 'error',
    //             'title' => __('admin::auth.verifyEmail.flashMessageErrorTitle'),
    //             'description' => __('admin::auth.verifyEmail.flashMessageErrorDescription'),
    //         ]);
    //         return redirect(RouteHelper::getRoute('/'));
    //     }

    //     // Log user in
    //     Auth::login($user, true);

    //     // Update user
    //     $user->email_verify_hash = null;
    //     $user->email_verified_at = new \DateTime();
    //     $user->save();

    //     session()->flash('message', [
    //         'color' => 'success',
    //         'title' => __('admin::auth.verifyEmail.flashMessageSuccessTitle'),
    //     ]);

    //     return redirect('/');
    // }

    /**
     * Sign out and redirect
     */
    public function logout()
    {
        Auth::logout();

        return redirect(route('admin.login'));
    }
}
