<?php

return [
    'name' => 'First Name',
    'email' => 'Email',
    'password' => 'Password',
    'display_name' => 'Display Name',
    'last_name' => 'Last Name',
    'address' => 'Address',
    'id' => 'id',
    'Auth' => 'Auth',
    'all_Auths' => 'All Auths',
    'admin title all Auth' => 'All Auth List',
    'roles' => 'Roles',
    'permissions' => 'Permissions',
    'remember_me' => 'Remember me',
    'Forgot your password?' => 'Forgot your password?',
    'Log in' => 'Log in',
    'Log Out' => 'Log Out',
    'sign_in' => 'Sign In',
    'enter_your_email_and_password_to_access_admin_panel' => 'Enter your email and password to access admin panel',
    'default' => 'Default',
    'phone' => 'Phone number',
    'status' => 'Status',
    'Authentication' => 'Authentication',
    'status_blocked' => 'Blocked',
    'status_active' => 'Active',
    'status_pending' => 'Pending',
    'information' => 'Auth Information',
    'roles_and_permissions' => 'Roles and Permissions',
    'change_password' => 'Change Password',
    'new_password' => 'New Password',
    'created_at' => 'Created at',
    'remeber' => 'Remeber',
    'language' => 'Language',
    'not_found_Auth' => 'Account with this email not found',
    'not_confirmed_Auth' => 'Your account is not verified. Please check your email and activate your account',
    'blocked_Auth' => 'Your account has been blocked. Please contact support at support@oiltime.com',
    'not_robot' => 'Please confirm that you are not a robot',
    'required' => 'This field is required',
    'only_letters' => 'Only letters are allowed',
    'max_size' => 'Maximum length is :max characters',
    'incorrect_credential' => 'Incorrect email or password',
    'email_validate' => [
        'required' => 'Email is required',
        'email' => 'Email must be in the correct format',
        'unique' => 'This email is already in use',
        'exists' => 'No account found with this email address',
    ],
    'password_validate' => [
        'required' => 'Password must contain at least :min characters',
        'required_only' => 'Password is required',
        'min' => 'Password must contain at least :min characters',
        'letters' => 'Password must contain at least one letter',
        'mixed' => 'Password must contain at least one uppercase and one lowercase letter',
        'numbers' => 'Password must contain at least one number',
        'symbols' => 'Password must contain at least one special character (e.g., @, #, $, !, %, ^, &, )',
        'confirmed' => 'Must match the "Password" field',
    ],
    'new_password_validate' => [
        'required' => 'New password must contain at least :min characters',
        'min' => 'New password must contain at least :min characters',
        'letters' => 'New password must contain at least one letter',
        'mixed' => 'New password must contain at least one uppercase and one lowercase letter',
        'numbers' => 'New password must contain at least one number',
        'symbols' => 'New password must contain at least one special character (e.g., @, #, $, !, %, ^, &, )',
        'confirmed' => 'Must match the "New password" field',
        'same_with_old_password' => 'The new password cannot be the same as the old password',
        'change' => 'Your password has been successfully changed',
    ],
    'ua_phone' => 'Format: +380XXXXXXXXX (12 characters, starts with +380)',
    'social' => [
        'continue_with' => 'Continue with :provider',
        'or_divider' => 'or',
        'unsupported_provider' => 'This sign-in method is not supported.',
        'failed' => 'Social sign-in failed. Please try again.',
        'google' => 'Google',
        'facebook' => 'Facebook',
    ],

    'email_verification' => [
        'success' => 'Email successfully verified',
        'expired' => 'Email verification time has expired',
        'already_verified' => 'Email is already verified',
        'not_verified' => 'Please verify your email address first. Check your inbox.',
        'subject' => 'Verify your email address',
        'preheader' => 'Confirm your email to complete registration.',
        'description' => 'Thanks for signing up! To finish creating your account, please verify your email address by clicking the button below.',
        'button' => 'Verify email',
        'expires_hint' => 'This link will expire in 60 minutes.',
        'fallback_hint' => 'If the button does not work, copy and paste this link into your browser:',
        'ignore_note' => 'If you did not create this account, just ignore this email.',
        'footer' => 'Best regards, the :name team',
    ],
    'mail_confirmation' => [
        'pls_verify' => 'An email has been sent to your email address to confirm your registration',
        'too_many_requests' => 'Пожалуйста, обратитесь в службу поддержки по адресу support@oiltime.com',
        'subject' => 'Registration on the OilTime platform',
        'greeting' => 'Dear :name,',
        'thanks' => 'Thank you for registering on the <strong>OilTime</strong> platform!',
        'confirm_text' => 'To complete the registration process, please confirm your email by clicking the link below:',
        'confirm_button' => 'Confirm Registration',
        'note' => '🔔 Note:',
        'validity' => 'This link will be valid for <strong>24 hours</strong>.',
        'repeat' => 'If you do not complete registration within this time, you will need to restart the process.',
        'ignore' => 'If you did not request registration, simply ignore this email.',
        'support' => 'If you encounter any difficulties, contact support at:',
        'team' => 'Best regards, <br> <strong>OilTime Team</strong>',
    ],
    'password_forgot' => [
        'send' => 'A password recovery link has been sent to your email address',
        'fail_send' => 'If you did not receive the link, please contact support',
        'expire_token' => 'The account activation link is invalid. Please try again.',
        'change' => 'Your password has been successfully changed. You can log in with your new password.',
        'subject' => 'Password Recovery',
        'preheader' => 'Set a new password in one click.',
        'description' => 'You are receiving this email because a password reset was requested for your account. Click the button below to set a new password.',
        'button' => 'Set a new password',
        'expires_hint' => 'This link will expire in 60 minutes.',
        'fallback_hint' => 'If the button does not work, copy and paste this link into your browser:',
        'ignore_note' => 'If you did not request a password reset, just ignore this email. Your password will remain unchanged.',
        'footer' => 'Best regards, the :name team',
        'content' => "
            <h2>Reset Your Password</h2>
            <p>Dear :name,</p>
            <p>You have requested to reset your password for your account on the <strong>OilTime</strong> platform.</p>
            <p>To create a new password, please click the link below:</p>
            <p><a href=':url' class='button'>Reset Password</a></p>
            <p><strong>🔔 Please Note:</strong></p>
            <ul>
                <li>This link will be valid for 24 hours.</li>
                <li>If you do not complete the password reset process within this time, you will need to request a new one.</li>
            </ul>
            <p><strong>Didn't request a password reset?</strong></p>
            <p>If you did not request a password reset, please ignore this email.</p>
            <p><strong>Need additional help?</strong></p>
            <p>If you did not receive the link or encountered other issues, please contact our support team: <a href='mailto:support@oiltime.com'>support@oiltime.com</a>.</p>
            <p>Best regards,<br><strong>The OilTime Team</strong></p>
        "
    ],
    'profile' => [
        'update' => 'Your information has been successfully updated'
    ],
    'pricelist' => [
        'send' => 'Your updated price list from OilTime',
        'fail_send' => 'Failed to upload the price list. Please try again later',
        'subject' => 'Your updated price list from OilTime :date',
        'content' => "
            <p>Dear :name,</p>
            <p>We have prepared the latest price list for you, which includes all the recent updates on prices and offers.</p>
            <p><a href=':url' class='button'>Download the price list</a></p>
            <p>This price list has been created for your convenience so that you always have access to the most up-to-date information on our products.</p>
            <p>Best regards,<br><strong>The OilTime Team</strong></p>
        "
    ],
    'activate_wholesaler' => [
        'subject' => 'Your account on the OilTime platform has been activated',
        'content' => "
            <p>Dear :name,</p>
            <p>We are pleased to inform you that your account on the <strong>OilTime</strong> platform has been successfully activated! You now have access to all the features and resources of the platform.</p>
            <p>If you experience any difficulties, please contact our support team at: <a href='mailto:support@oiltime.com'>support@oiltime.com</a>.</p>
            <p>We are happy to have you among our Auths!</p>
            <p>Best regards,<br><strong>The OilTime Team</strong></p>
        "
    ],
    'delete_wholesaler' => [
        'subject' => 'Account Deletion on the OilTime Platform',
        'content' => "
            <p>Dear :name,</p>
            <p>We would like to inform you that your account on the OilTime platform has been deleted. This may be the result of the termination of your collaboration with our company or the conclusion of your need for access to our platform.</p>
            <p>Your account has been deleted, and access to the OilTime platform has been terminated. If this was done in error, please contact us using the following details.</p>
            <p>If you experience any difficulties, please reach out to our support team at: <a href='mailto:support@oiltime.com'>support@oiltime.com</a>.</p>
            <p>Best regards,<br><strong>The OilTime Team</strong></p>
        "
    ],
    'Auth_type' => 'Type',
    'admin' => 'Admin',
    'customer' => 'Customer',
    'store' => 'Store',
    'wholesaler' => 'Wholesaler',
    'docs' => [
        'title' => 'Authentication System',
        'subtitle' => 'Ensuring secure login and session management.',
        'guards_title' => 'Guards & Providers',
        'guards_desc' => 'The system uses standard Laravel guards for `web` sessions and `api` tokens. Providers are configured to work with the `User` model.',
        'tech_title' => 'Technical Details',
        'admin_part' => 'Admin Part',
        'api_part' => 'API Part',
        'endpoints' => 'Endpoints',
        'fields' => 'Fields',
    ],
    'module_name' => 'Auth',
];
