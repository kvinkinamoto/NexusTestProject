<?php

return [
  'index_title' => 'Users',
  'chooseTrashed' => 'Choose trashed',
  'withTrashed' => 'With trashed',
  'onlyTrashed' => 'Only trashed',
  'name' => 'First Name',
  'display_name' => 'Display Name',
  'display name' => 'Display Name',
  'last_name' => 'Last Name',
  'address' => 'Address',
  'password' => 'Password',
  'id' => 'id',
  'user' => 'User',
  'User' => 'Users',
  'email' => 'Email',
  'all_users' => 'All Users',
  'admin title all user' => 'All User List',
  'PENDING' => 'Pending',
  'ACTIVE' => 'Active',
  'BLOCKED' => 'Blocked',
  'roles' => 'Roles',
  'permissions' => 'Permissions',
  'remember_me' => 'Remember me',
  'Forgot your password?' => 'Forgot your password?',
  'Log in' => 'Log in',
  'Log Out' => 'Log Out',
  'default' => 'Default',
  'phone' => 'Phone number',
  'status' => 'Status',
  'status_blocked' => 'Blocked',
  'status_active' => 'Active',
  'status_pending' => 'Pending',
  'information' => 'Information',
  'roles_and_permissions' => 'Roles and Permissions',
  'change_password' => 'Change Password',
  'new_password' => 'New Password',
  'created_at' => 'Created at',
  'language' => 'Language',
  'not_found_user' => 'Account with this email not found',
  'not_confirmed_user' => 'Your account is not verified. Please check your email and activate your account',
  'blocked_user' => 'Your account has been blocked. Please contact support at support@oiltime.com',
  'not_robot' => 'Please confirm that you are not a robot',
  'required' => 'This field is required',
  'only_letters' => 'Only letters are allowed',
  'max_size' => 'Maximum length is :max characters',
  'incorrect_credential' => 'Incorrect email or password',
  'email_validate' =>
  [
    'required' => 'Email is required',
    'email' => 'Email must be in the correct format',
    'unique' => 'This email is already in use',
    'exists' => 'No account found with this email address',
  ],
  'password_validate' =>
  [
    'required' => 'Password must contain at least :min characters',
    'min' => 'Password must contain at least :min characters',
    'letters' => 'Password must contain at least one letter',
    'mixed' => 'Password must contain at least one uppercase and one lowercase letter',
    'numbers' => 'Password must contain at least one number',
    'symbols' => 'Password must contain at least one special character (e.g., @, #, $, !, %, ^, &, ]',
    'confirmed' => 'Must match the "Password" field',
  ],
  'new_password_validate' =>
  [
    'required' => 'New password must contain at least :min characters',
    'min' => 'New password must contain at least :min characters',
    'letters' => 'New password must contain at least one letter',
    'mixed' => 'New password must contain at least one uppercase and one lowercase letter',
    'numbers' => 'New password must contain at least one number',
    'symbols' => 'New password must contain at least one special character (e.g., @, #, $, !, %, ^, &, ]',
    'confirmed' => 'Must match the "New password" field',
    'same_with_old_password' => 'The new password cannot be the same as the old password',
    'change' => 'Your password has been successfully changed',
  ],
  'ua_phone' => 'Format: +380XXXXXXXXX (12 characters, starts with +380]',
  'email_verification' =>
  [
    'success' => 'Email successfully verified',
    'expired' => 'Email verification time has expired',
    'already_verified' => 'Email is already verified',
    'exprired' => 'Email verification time has expired.',
  ],
  'mail_confirmation' =>
  [
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
  'password_forgot' =>
  [
    'send' => 'A password recovery link has been sent to your email address',
    'fail_send' => 'If you did not receive the link, please contact support',
    'expire_token' => 'The account activation link is invalid. Please try again.',
    'change' => 'Your password has been successfully changed. You can log in with your new password.',
    'subject' => 'Password Recovery on OilTime Platform',
    'content' => '
            <h2>Reset Your Password</h2>
            <p>Dear :name,</p>
            <p>You have requested to reset your password for your account on the <strong>OilTime</strong> platform.</p>
            <p>To create a new password, please click the link below:</p>
            <p><a href=\':url\' class=\'button\'>Reset Password</a></p>
            <p><strong>🔔 Please Note:</strong></p>
            <ul>
                <li>This link will be valid for 24 hours.</li>
                <li>If you do not complete the password reset process within this time, you will need to request a new one.</li>
            </ul>
            <p><strong>Didn\'t request a password reset?</strong></p>
            <p>If you did not request a password reset, please ignore this email.</p>
            <p><strong>Need additional help?</strong></p>
            <p>If you did not receive the link or encountered other issues, please contact our support team: <a href=\'mailto:support@oiltime.com\'>support@oiltime.com</a>.</p>
            <p>Best regards,<br><strong>The OilTime Team</strong></p>
        ',
  ],
  'profile' =>
  [
    'update' => 'Your information has been successfully updated',
  ],
  'pricelist' =>
  [
    'send' => 'Your updated price list from OilTime',
    'fail_send' => 'Failed to upload the price list. Please try again later',
    'subject' => 'Your updated price list from OilTime :date',
    'content' => '
            <p>Dear :name,</p>
            <p>We have prepared the latest price list for you, which includes all the recent updates on prices and offers.</p>
            <p><a href=\':url\' class=\'button\'>Download the price list</a></p>
            <p>This price list has been created for your convenience so that you always have access to the most up-to-date information on our products.</p>
            <p>Best regards,<br><strong>The OilTime Team</strong></p>
        ',
  ],
  'activate_wholesaler' =>
  [
    'subject' => 'Your account on the platform has been activated',
    'content' => '<p>Dear :name,</p>
            <p>We are pleased to inform you that your account on the <strong></strong> platform has been successfully activated! You now have access to all the features and resources of the platform.</p>
            <p>If you experience any difficulties, please contact our support team at: <a href=\'mailto:\'></a>.</p>
            <p>We are happy to have you among our users!</p>
            <p>Best regards,<br><strong></strong></p>',
  ],
  'delete_wholesaler' =>
  [
    'subject' => 'Account Deletion on the Platform',
    'content' => '<p>Dear :name,</p>
            <p>We would like to inform you that your account on the platform has been deleted. This may be the result of the termination of your collaboration with our company or the conclusion of your need for access to our platform.</p>
            <p>Your account has been deleted, and access to the OilTime platform has been terminated. If this was done in error, please contact us using the following details.</p>
            <p>If you experience any difficulties, please reach out to our support team at: <a href=\'mailto:\'></a>.</p>
            <p>Best regards,<br><strong></strong></p>',
  ],
  'user_type' => 'Type',
  'admin' => 'Admin',
  'customer' => 'Customer',
  'store' => 'Store',
  'wholesaler' => 'Wholesaler',
  'menu_users' => 'users',
  'Admin' => 'Admin',
  'Customer' => 'Customer',
  'docs' =>
  [
    'title' => 'User Management',
    'subtitle' => 'System for managing accounts, profiles, and access control.',
    'roles_permissions_title' => 'Roles & Permissions',
    'roles_permissions_desc' => 'Each user can be assigned multiple roles and individual permissions for granular access control.',
    'password_title' => 'Password Management',
    'password_desc' => 'Administrators can force password changes. The system supports secure hashing and complexity validation rules.',
    'tech_title' => 'Technical Details',
    'admin_part' => 'Admin Part',
    'api_part' => 'API Part',
    'endpoints' => 'Endpoints',
    'fields' => 'Fields',
  ],
  'Permission' => 'Permission',
  'Email' => 'Email',
  'First name' => 'First name',
  'Roles' => 'Roles',
  'module_name' => 'User',
  'wishlist' => 'Wishlist',
  'wishlist_section' => 'Wishlist',
  'addresses_section' => 'Addresses',
  'wishlist_type' => 'Type',
  'wishlist_item' => 'Item',
  'wishlist_added_at' => 'Added at',
  'no_wishlist_items' => 'User has no wishlist items.',
  'cart' => 'Cart',
  'cart_section' => 'Cart',
  'no_cart_products' => 'User has no products in cart.',
  'open_cart' => 'Open in Cart module',
  'shopping' => 'Cart & Wishlist',
  'Name' => 'Name',
  'old_password' =>
  [
    'required' => 'Old password is required.',
    'exists' => 'The old password was entered incorrectly.',
  ],
  'activate_whosaler' =>
  [
    'subject' => 'Your account has been activated.',
    'content' => '<p>Dear :name,</p>
<p>We are pleased to inform you that your account on the <strong></strong> platform has been successfully activated! You now have access to all the platform\'s features and resources.</p>
<p>If you have any difficulties, please contact the support service: <a href=\'mailto:\'></a>.</p>
<p>We are glad to see you among our users!</p>
<p>Best wishes,<br><strong></strong></p>',
  ],
  'delete_whosaler' =>
  [
    'subject' => 'Deleting an account on the platform',
    'content' => '<p>Dear :name,</p>
<p>We would like to inform you that your account on the platform has been deleted. This may be the result of your termination of cooperation with our company or the termination of the need for access to our platform.</p>
<p>Your account has been deleted and access to the platform has been terminated. If this happened in error, please contact us using the following contacts.</p>
<p>If you have any difficulties, please contact support: <a href=\'mailto:\'></a>.</p>
<p>Best wishes,<br><strong>Team </strong></p>',
  ],
  'enum_genders' => [
    'MALE' => 'Male',
    'FEMALE' => 'Female',
  ],
  'addresses' => 'Addresses',
  'no_addresses' => 'User has no saved addresses.',
  'created_at' => 'Created at',
  'city' => 'City',
  'street' => 'Street',
  'house' => 'House',
  'apartment' => 'Apartment',
  'is_main' => 'Main',
  'middle_name' => 'Middle name',
  'gender' => 'Gender',
  'birthday' => 'Birthday',
  'avatar' => 'Avatar',
  'current_password' => 'Current password',
  'password_confirmation' => 'Password confirmation',
];
