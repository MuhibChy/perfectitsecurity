<?php

/**
 * Role-based verification policy (administrator-configurable).
 * Identity verification and 2FA are INDEPENDENT states: one never implies
 * the other. Enforcement points document where each rule bites.
 */
return [
    'identity' => [
        // Roles that must complete identity verification.
        'required_for' => ['employee', 'finance_manager', 'admin', 'super_admin', 'project_manager', 'support_manager'],
        // Roles where it is optional unless management requests it.
        'optional_for' => ['customer', 'freelancer', 'commission_agent', 'support_agent', 'sales_agent', 'training_manager'],
        // Accepted document types (IdentityDocument::TYPES subset).
        'accepted_types' => ['national_id', 'passport', 'driving_licence'],
        // Expiry policy: manual | document_expiry. Never silently un-verify.
        'expiry_policy' => 'manual',
    ],
    'two_factor' => [
        // Roles where 2FA is mandatory once enrolled-capable.
        'required_for' => ['admin', 'super_admin', 'finance_manager'],
        // Everyone else: available, strongly recommended.
        'available_for' => ['*'],
        'methods' => ['totp', 'recovery_codes'],
    ],
];
