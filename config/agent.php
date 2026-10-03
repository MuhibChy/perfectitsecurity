<?php

return [
    /*
    |--------------------------------------------------------------------------
    | AI Agent Gateway — thin adapter layer (extends, never replaces AI stack)
    |--------------------------------------------------------------------------
    | All values env-driven. Safe defaults: everything external is OFF.
    | Existing chat (AiChatService + OllamaProvider) is unaffected by these
    | switches unless AI_CHAT_ENABLED=false explicitly disables it.
    */

    // Master kill switch. false => AgentGateway refuses all agent calls.
    'enabled' => env('AI_AGENT_ENABLED', false),

    // Which external runtime the gateway may delegate to when verified.
    // none|hermes|openclaw — replaceable without touching business code.
    'runtime' => env('AI_AGENT_RUNTIME', 'none'),

    // Optional HTTP endpoint for a verified runtime proxy/gateway.
    // Leave empty until staging verification proves a documented endpoint.
    'endpoint' => env('AI_AGENT_ENDPOINT', ''),

    'timeout' => (int) env('AI_AGENT_TIMEOUT', 60),
    'connect_timeout' => (int) env('AI_AGENT_CONNECT_TIMEOUT', 10),

    // Independent switches (prefer separate switches per spec §19).
    'chat_enabled' => env('AI_CHAT_ENABLED', true),
    'hermes_enabled' => env('HERMES_ENABLED', false),
    'openclaw_enabled' => env('OPENCLAW_ENABLED', false),
    'omniroute_enabled' => env('OMNIROUTE_ENABLED', false),

    // Cost / load guards.
    'max_tokens' => (int) env('AI_AGENT_MAX_TOKENS', 512),
    'max_concurrency' => (int) env('AI_AGENT_MAX_CONCURRENCY', 4),

    // When true, MEDIUM tools also require explicit human approval record.
    'require_approval' => env('AI_AGENT_REQUIRE_APPROVAL', true),

    // Allowlisted tools the gateway may ever execute (subset of AiAgentService).
    // HIGH-risk actions are intentionally absent — gateway denies them by default.
    'allowed_tools' => explode(',', (string) env(
        'AI_AGENT_ALLOWED_TOOLS',
        'search_knowledge_base,search_services,get_customer_tickets,get_ticket_status,'
        .'get_order_status,get_project_status,get_customer_invoice_status,'
        .'get_authenticated_user,get_customer_profile,create_support_ticket,'
        .'add_ticket_message,create_service_request,escalate_to_employee,'
        .'get_assigned_tickets,get_assigned_tasks,generate_service_report,'
        .'create_work_log,summarize_customer_issues'
    )),

    // Roles permitted to invoke the agent gateway at all.
    'allowed_roles' => explode(',', (string) env(
        'AI_AGENT_ALLOWED_ROLES',
        'customer,support_agent,project_manager,finance_manager,employee,'
        .'freelancer,admin,super_admin,support_manager,sales_agent,'
        .'training_manager,commission_agent'
    )),
];
