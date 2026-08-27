<?php

return [
    'logres' => [
        'name' => 'Logres',
        'availability' => 'In development',
        'summary' => 'Hosted software-development orchestration for turning requests into executable tasks and verified results.',
        'description' => 'Logres accepts a software-development request, translates it into explicit tasks and prompts, dispatches those tasks to isolated execution targets, streams execution feedback, and returns the resulting code, tests, and artifacts.',
        'delivery' => 'Logres is a digitally delivered hosted service. Account access is provided online after successful signup and any required payment.',
        'documentation' => [
            'status' => 'Planned MVP contract',
            'purpose' => 'Give a person one place to request software work, review the executable plan, follow agent execution, answer questions, and receive a consolidated result.',
            'audiences' => [
                'People submitting development requests from a desktop or mobile client',
                'Operators reviewing execution progress and failures',
                'Product applications integrating with the Logres request API',
            ],
            'inputs' => [
                'An authenticated account with the logres.access entitlement',
                'A natural-language software-development request',
                'The authorized project or workspace context',
                'Optional constraints, acceptance criteria, and supporting assets',
            ],
            'workflow' => [
                [
                    'name' => 'ExecutionRequest',
                    'description' => 'Logres receives the caller’s request and records the desired outcome, supplied context, and constraints.',
                ],
                [
                    'name' => 'Translated Tasks',
                    'description' => 'The request becomes an ordered set of bounded tasks with dependencies and completion criteria.',
                ],
                [
                    'name' => 'TaskPrompt',
                    'description' => 'Each task receives an explicit prompt containing only the instructions and context required for that unit of work.',
                ],
                [
                    'name' => 'ExecutionTarget selection',
                    'description' => 'Logres selects an explicit isolated target for each task. The MVP uses an Orb target; later adapters may support other virtual-machine providers.',
                ],
                [
                    'name' => 'Orb API dispatch',
                    'description' => 'The task prompt and authorized workspace policy are sent to the selected Orb through its API.',
                ],
                [
                    'name' => 'Execution feedback',
                    'description' => 'Logs, state changes, artifacts, failures, and questions flow back to Logres as execution events.',
                ],
                [
                    'name' => 'Caller response',
                    'description' => 'When execution needs a decision or missing input, the caller can answer and the response returns to the waiting task.',
                ],
                [
                    'name' => 'Aggregated response',
                    'description' => 'Logres combines task outcomes into one final response with the resulting code, tests, artifacts, and unresolved issues.',
                ],
            ],
            'outputs' => [
                'A traceable task plan derived from the original request',
                'Live execution status, feedback, and requests for input',
                'Code, tests, documents, screenshots, or other produced artifacts',
                'A final result that distinguishes completed work, failures, and unresolved decisions',
            ],
            'interactions' => [
                'Submit a request through an authenticated API from mobile, desktop, or another product',
                'Review tasks and the execution target selected for each task',
                'Follow execution events without waiting for the complete run',
                'Respond directly when an executing task requests input',
                'Review the aggregated result and its supporting artifacts',
            ],
            'agents' => [
                'Codex',
                'Claude',
                'Amp',
                'Grok',
                'Additional agents that satisfy the shared execution-agent contract',
            ],
            'data_boundaries' => [
                'Accounts owns account identity, product access, and billing-derived entitlements.',
                'Logres owns execution requests, tasks, prompts, run state, events, and result references.',
                'Orb owns the isolated MVP execution environment and returns execution feedback through its API.',
                'Stripe owns payment instruments, checkout, subscriptions, invoices, and refunds.',
                'The selected identity provider will own credentials, sessions, multifactor authentication, and recovery.',
            ],
            'limits' => [
                'Logres is in development; this page describes the intended MVP behavior, not a currently available execution service.',
                'Orb is the MVP execution target. Additional target providers are future integrations behind a shared target contract.',
                'Workspace authorization, maximum run duration, prompt transport, and direct process-launch policy still require explicit decisions before execution is enabled.',
                'Agent availability and capabilities depend on configured provider access and the selected execution target.',
                'NativePHP is planned after the Laravel Cloud MVP; the first client surface is web and API based.',
            ],
        ],
        'plans' => [
            'standard' => [
                'name' => 'Standard',
                'price' => env('BUSINESS_LOGRES_PRICE'),
                'currency' => env('BUSINESS_LOGRES_CURRENCY', 'USD'),
                'billing_period' => env('BUSINESS_LOGRES_BILLING_PERIOD', 'month'),
                'stripe_price_id' => env('STRIPE_LOGRES_PRICE_ID'),
                'entitlement' => 'logres.access',
                'features' => [
                    'Submit software-development execution requests',
                    'Receive translated tasks and execution feedback',
                    'Review resulting code, tests, and artifacts',
                ],
            ],
        ],
    ],
];
