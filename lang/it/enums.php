<?php

return [
    'asset_status' => [
        'available' => 'Disponibile',
        'assigned' => 'Assegnato',
        'maintenance' => 'In manutenzione',
        'retired' => 'Dismesso',
        'lost' => 'Smarrito',
    ],
    'company_status' => [
        'active' => 'Attiva',
        'inactive' => 'Inattiva',
        'prospect' => 'Potenziale',
        'suspended' => 'Sospesa',
    ],
    'company_type' => [
        'customer' => 'Cliente',
        'supplier' => 'Fornitore',
        'partner' => 'Partner',
        'prospect' => 'Potenziale cliente',
        'other' => 'Altro',
    ],
    'project_priority' => [
        'low' => 'Bassa',
        'medium' => 'Media',
        'high' => 'Alta',
        'urgent' => 'Urgente',
    ],
    'project_status' => [
        'planned' => 'Pianificato',
        'active' => 'Attivo',
        'on_hold' => 'In pausa',
        'completed' => 'Completato',
        'cancelled' => 'Annullato',
    ],
    'task_priority' => [
        'low' => 'Bassa',
        'medium' => 'Media',
        'high' => 'Alta',
        'urgent' => 'Urgente',
    ],
    'task_status' => [
        'todo' => 'Da fare',
        'in_progress' => 'In corso',
        'blocked' => 'Bloccata',
        'completed' => 'Completata',
        'cancelled' => 'Annullata',
    ],
    'task_recurrence' => [
        'none' => 'Non si ripete',
        'daily' => 'Giornaliera',
        'weekly' => 'Settimanale',
        'monthly' => 'Mensile',
    ],
    'automation_trigger' => [
        'task_overdue' => 'Attività scaduta',
        'task_due_soon' => 'Attività in scadenza',
        'ticket_sla_breached' => 'SLA ticket superato',
        'project_due_soon' => 'Progetto in scadenza',
    ],
    'automation_action' => [
        'notify_assignee' => 'Notifica assegnatario',
        'notify_manager' => 'Notifica responsabile progetto',
        'notify_user' => 'Notifica utente selezionato',
        'set_ticket_priority' => 'Imposta priorità ticket',
    ],
    'ticket_category' => [
        'general' => 'Generale',
        'technical' => 'Tecnico',
        'access' => 'Accesso',
        'billing' => 'Fatturazione',
        'request' => 'Richiesta',
        'other' => 'Altro',
    ],
    'ticket_priority' => [
        'low' => 'Bassa',
        'medium' => 'Media',
        'high' => 'Alta',
        'urgent' => 'Urgente',
    ],
    'ticket_status' => [
        'open' => 'Aperto',
        'in_progress' => 'In lavorazione',
        'waiting' => 'In attesa',
        'resolved' => 'Risolto',
        'closed' => 'Chiuso',
    ],
];
