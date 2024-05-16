<?php

return [
    'sms_join_app' => 'Olá :name, seu amigo :name_friend está tentando se conectar com você usando o aplicativo Mou. Por favor baixe clicando aqui',
    'sms_event_join_app' => 'Oi, :user_invite convidou você para :event_title em :date às :hour em :place. Para aceitar o convite é necessário primeiro baixar o aplicativo Mou. A equipe Mou espera que você se diverta! Link abaixo',
    'add_employee_to_roster_body' => 'Você recebeu uma nova escala de :creator_name',
    'create_todo' => ':creator_name acabou de criar um To-Do :todo_title e marcou você, agora vocês dois têm acesso a ele',
    //Event notify
    'accept_event' => ':user acabou de aceitar seu convite para :event_title em :date às :hour :place',
    'create_event' => ':creator_name convidou você para :event_title em :date às :hour :place',
    'create_24h_event' => 'O :event_title que :creator_name convidou você será amanhã às :hour :place, não esqueça de aceitá-lo',
    'deny_event' => 'Infelizmente, :user não aceitou seu convite para :event_title em :date às :hour :place',
    'user_cancel_invitation' => ':user  acabou de cancelar o convite aceito para :event_title em :date às :hour :place',
    'delete_event' => ':creator_name cancelou o :event_title em :date às :hour :place',
    'edit_event' => ':creator_name alterou o evento que agora é :event_title em :date em :hour :place',
    'alarm_event' => ':event_title começará em :alarm :place',
    //Company
    'add_to_company' => ':company_name enviou a você uma solicitação para ingressar na empresa como :job_title',
    //Project
    'assign_leader' => ':company_name acaba de nomeá-lo como líder do projeto :project_title',
    'create_task_in_project' => ':company_name atribuiu a você uma tarefa :task_title para :date no projeto :project_title liderado por :leader',
    'edit_task_in_project' => ':company_name alterou a tarefa para :task_title para :date no projeto :project_title liderado por :leader',
    'mark_complete_previous_task_project' => ':company_name gostaria que você soubesse que a tarefa anterior :task_title foi concluída do projeto :project_title liderado pelo :leader',
    //Employee
    'employee_accept_join_to_company_body' => ':employee_name não aceitou o convite para ingressar na empresa como :role',
    'employee_deny_join_to_company_body' => ':employee_name não aceitou o convite para ingressar na empresa como :role',
    'user_accept_task_project' => ':user_name aceitou a tarefa :task_title em :date para o projeto :project_title liderado por :leader',
    'user_decline_accept_task_project' => ':user_name não aceitou a tarefa :task_title em :date para o projeto :project_title liderado por :leader',
    'user_mark_task_complete_project' => ':user_name marcou a tarefa :task_title como concluída para o projeto :project_title liderado por :leader',
    'user_mark_task_not_complete_project' => ':user_name marcou a tarefa :task_title como não concluída para o projeto :project_title liderado por :leader',
    //Task
    'create_task' => ':company_name atribuiu a você uma tarefa :task_title em :date na loja :store_name',
    //Roster
    'send_roster' => ':company_name enviou a você uma escala para :day às :hour na loja :store_name',
    'employee_accept_roster_body' => ':employee_name aceitou a escala em :date de :start_hour a :finish_hour na loja :store_name',
    'employee_decline_roster_body' => ':employee_name não aceitou a escala em :date de :start_hour a :finish_hour na loja :store_name',
    //Event
    'event_start' => 'Oi, :event_title começa agora',
    //Roster
    'user_cancel_after_accept' => ':employee_name cancelou a escalação em :date de :start_hour a :finish_hour na loja :store_name',
    'roster_start' => 'Oi, sua escala começa agora até ás :finish_time em :store name',
    'edit_roster' => ':company_name mudou a escala para começar as :start_time de :start_hour a :finish_time em :store_name',
    //Action task
    'user_accept_task' => ':user_name aceitou a tarefa :task_title em :date :store_name',
    'user_decline_accept_task' => ':user_name não aceitou a tarefa :task_title em :date :store_name',
    'employee_done_task' => ':user_name marcou a tarefa :task_title como concluída',
    'user_mark_task_not_complete' => ':user_name marcou a tarefa :task_title como não concluída',

    'edit_task' => ':company_name changed the task to :task_title on :date in the store :store_name',
    //not response
    'not_response_roster' => 'Você não respondeu ao convite da escala de hoje a tempo',
    'not_response_event' => 'Você não respondeu ao convite do :event_title hoje a tempo',
    'not_response_task' => 'Você não respondeu ao convite da tarefa :task_title a tempo na :store_name',
    'not_response_project' => 'Você não respondeu ao convite da tarefa :task_title a tempo do projeto :project_title liderado pelo :leader',
    'send_creator_when_not_response_roster' => ':user não respondeu ao convite da escala até o horário de início na :store_name',
    'send_creator_when_not_response_task' => ':user não respondeu ao convite da tarefa :task_title até o horário de início',
    'send_creator_when_not_response_project' => ':user não respondeu ao convite da tarefa :task_title" até o horário de início do projeto :project_title liderado pelo :leader',
];
