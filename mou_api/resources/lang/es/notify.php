<?php

return [
    'sms_join_app' => 'Hola :name, tu amigo :name_friend está tratando de conectarse contigo usando la aplicación Mou. Descargue haciendo clic aquí.',
    'sms_event_join_app' => ' Hola, :user_invite te invitó al :event_title" en :date a las :hour en :place. Para aceptar la invitación primero debes descargar la aplicación Mou. ¡El equipo de Mou espera que os divirtáis! Link debajo',
    'add_employee_to_roster_body' => 'Ha recibido una nueva solicitud de escala por :creator_name',
    'create_todo' => ':creator_name acaba de crear uno To-Do :todo_title y lo etiquetó, ahora ambos tienen acceso a él',
    //Event notify
    'accept_event' => ':user acaba de aceptar su invitación al :event_title en :date a las :hour :place',
    'create_event' => ':creator_name lo invitó al :event_title en :date a las :hour :place',
    'create_24h_event' => 'El :event_title al que te invitó :creator_name será mañana a las :hour :place, no olvides aceptarlo',
    'deny_event' => ':user lamentablemente no aceptó su invitación para :event_title en la :date a las :hour :place',
    'user_cancel_invitation' => ':user acaba de cancelar la invitación aceptada para :event_title en la :date a las :hour :place',
    'delete_event' => ':creator_name canceló el :event_title el :date a las :hour :place',
    'edit_event' => ':creator_name cambió el evento que ahora es :event_title en :date en :hour :place',
    'alarm_event' => ':event_title comenzará en :alarm :place',
    //Company
    'add_to_company' => ':company_name le envió una solicitud para unirse a la empresa como :job_title',
    //Project
    'assign_leader' => ':company_name acaba de nombrarle líder del proyecto :project_title',
    'create_task_in_project' => ':company_name le ha asignado una tarea :task_title para la :date en el proyecto :project_title dirigido por :leader',
    'edit_task_in_project' => ':company_name cambió la tarea a :task_title para :date  en el proyecto :project_title dirigido por :leader',
    'mark_complete_previous_task_project' => ':company_name quisiera saber si la tarea anterior :task_title fue completada por el proyecto :project_title dirigido por el :leader',
    //Employee
    'employee_accept_join_to_company_body' => ':employee_name no ha aceptado la invitación para unirse a la empresa como :role',
    'employee_deny_join_to_company_body' => ':employee_name no ha aceptado la invitación para unirse a la empresa como :role',
    'user_accept_task_project' => ':user_name aceptó la tarea :task_title en la :date para el proyecto :project_title dirigido por el :leader',
    'user_decline_accept_task_project' => ':user_name na ha aceptado la tarea :task_title en la :date para el proyecto :project_title dirigido por el :leader',
    'user_mark_task_complete_project' => ':user_name marcó la tarea :task_title como completada para el proyecto :project_title dirigido por :leader',
    'user_mark_task_not_complete_project' => ':user_name marcó la tarea :task_title como no completada para el proyecto :project_title dirigido por :leader',
    //Task
    'create_task' => ':company_name le asignó una tarea :task_title el :date en la tienda :store_name',
    //Roster
    'send_roster' => ':company_name le envió un escala de trabajo para el :day a las :hour en la tienda :store_name',
    'employee_accept_roster_body' => ':employee_name aceptó la escala de trabajo en :date desde la :start_hour hasta la :finish_hour en la tienda :store_name',
    'employee_decline_roster_body' => ':employee_name no ha aceptado la escala de trabajo en la :date desde la :start_hour hasta la :finish_hour en la tienda :store_name',
    //Event
    'event_start' => 'Hola, :event_title comienza ahora',
    //Roster
    'user_cancel_after_accept' => ':employee_name ha cancelado la lista en la :date desde la :start_hour hasta la :finish_hour en la tienda :store_name',
    'roster_start' => 'Hola, tu escala de trabajo comienza ahora hasta la :finish_time en :store name',
    'edit_roster' => ':company_name cambió la escala de trabajo para comenzar en la :start_time desde la :start_hour hasta la :finish_time en el :store_name',
    //Action task
    'user_accept_task' => ':user_name aceptó la tarea :task_title el :date :store_name',
    'user_decline_accept_task' => ':user_name no ha aceptado la tarea :task_title en la :date :store_name',
    'employee_done_task' => ':user_name marcó la tarea :task_title como completada',
    'user_mark_task_not_complete' => ':user_name marcó la tarea :task_title como no completada',

    'edit_task' => ':company_name changed the task to :task_title on :date in the store :store_name',

    //not response
    'not_response_roster' => 'No respondiste a tiempo a la invitación de la escala de trabajo de hoy',
    'not_response_event' => 'No respondiste a tiempo a la invitación de la escala de trabajo de hoy',
    'not_response_task' => 'No respondiste a la invitación de la tarea :task_title a tiempo en el :store_name',
    'not_response_project' => 'No respondió a tiempo a la invitación de la tarea :task_title del proyecto :project_title dirigido por el :leader',
    'send_creator_when_not_response_roster' => ':user no respondió a la invitación de la escala de trabajo a la hora de inicio en :store_name',
    'send_creator_when_not_response_task' => ':user no respondió a la invitación de tarea :task_title a la hora de inicio',
    'send_creator_when_not_response_project' => ':user no respondió a la invitación de la tarea :task_title" antes de la hora de inicio del proyecto :project_title dirigido por :leader',
];
