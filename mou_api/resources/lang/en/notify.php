<?php

return [

    'add_employee_to_company' => 'You receive an invitation to join :Company_name company from :Creator_name',
    'add_employee_to_company_title' => 'You have received 1 new invitation',
    'employee_accept_join_to_company_title' => 'You have 1 new notification',
    'employee_deny_join_to_company_title' => 'You have 1 new notification',
    'employee_accept_join_to_company_body' => ':employee_name accepted the invitation to join the company as :role',
    'employee_deny_join_to_company_body' => ':employee_name has not accepted the invitation to join the company as :role',

    'employee_done_task_or_project_task_title' => ':project_name You have 1 new notification',
    'employee_done_task' => ':employee_name marked the task :task_name as completed',
    'employee_done_project_task' => ':employee_name has marked this project done',

    'employee_responsible_create_project_title' => 'Project :project_name: you have a new notification',
    'employee_responsible_create_project_body' => ':creator_name has just appointed you as the person in charge of the project',
    'employee_join_project_title' => 'Project :project_name you have a new notification',
    'employee_join_project_body' => ':creator_name just added you to the project team',
    'employee_responsible_edit_project_title' => 'Project :project_name: you have a new notification',
    'employee_responsible_edit_project_body' => ':creator_name has just appointed you as the person in charge of the project',

    'employee_leave_project_title' => 'Project :project_name You have a new notification',
    'employee_leave_project_body' => 'Employee :employee_name has left this project',

    'person_responsible_leave_project_title' => 'Project :project_name: You have a new notification',
    'person_responsible_leave_project_body' => 'The responsible person has left this project',

    'employee_action_task_title' => 'You have a new notification',
    'employee_action_project_task_title' => 'Project :project_name: You have a new notification',
    'employee_action_task_or_project_task' => 'Employee :employees_name has :status :project_or_task :task_name',

    'sms_join_app' => 'Hi :name, your friend :name_friend is trying to connect with you using the app Mou. Please download clicking here',
    'sms_event_join_app' => 'Hi, :user_invite  invited you for :event_title on :date at :hour at :place. To accept the invitation it is necessary to download Mou app first. Mou team hope you can have fun! Link below',
    'employee_create_task_title' => 'Task :task_name: you have a new notification',
    'employee_create_task_body' => ':creator_name just added you to the task',

    'add_employee_to_roster_title' => 'Roster - you have a new notification',
    'add_employee_to_roster_body' => 'You have received a new roster request by :creator_name',
    'employee_action_roster_title' => 'Roster - you have a new notification',
    'employee_accept_roster_body' => ':employee_name accepted the roster on :date from :start_hour to :finish_hour in the store :store_name',
    'employee_decline_roster_body' => ':employee_name has not accepted the roster on :date from :start_hour to :finish_hour in the store :store_name',

    'create_todo' => ':creator_name just created a To-Do :todo_title and tagged you, now you both have access to it',
    //Event notify
    'create_event' => ':creator_name invited you to :event_title on :date at :hour :place',
    'accept_event' => ':user just accepted your invitation for :event_title on :date at :hour :place',
    'deny_event' => ":user unfortunately didn't accept your invitation for :event_title on :date at :hour :place",
    'create_24h_event' => "The :event_title that :creator_name invited you will be tomorrow at :hour :place, don't forget to accept it",
    'event_start' => 'Hi, :event_title start now',
    'user_cancel_invitation' => ':user just canceled the accepted invitation for :event_title on :date at :hour :place',
    'delete_event' => ':creator_name canceled the :event_title on :date at :hour :place',
    'edit_event' => ':creator_name changed the event that now is :event_title on :date :hour :place',
    'alarm_event' => ':event_title will start in :alarm :place',
    //Company
    'add_to_company' => ':company_name sent you a request to join the company as :job_title',
    //Roster
    'send_roster' => ':company_name sent you a roster on :day at :hour in the store :store_name',
    'roster_start' => 'Hi, your roster starts now until :finish_time at :store_name',
    'edit_roster' => ':company_name changed the roster for starting on :start_time from :start_hour to :finish_time at :store_name',
    'send_creator_when_not_response_roster' => ':user did not respond to  the roster invitation by the start time for the :store_name',
    //Project
    'assign_leader' => ':company_name just appointed you as a leader for the project :project_title',
    'create_task_in_project' => ':company_name assigned you a task :task_title on :date in the project :project_title led by :leader',
    'edit_task_in_project' => ':company_name changed the task to :task_title on :date in the project :project_title led by :leader',
    'mark_complete_previous_task_project' => ':company_name would like you to know that the previous task :task_title was completed from the project :project_title led by :leader',
    'user_accept_task_project' => ':user_name accepted the task :task_title on :date for the project :project_title led by :leader',
    'user_decline_accept_task_project' => ':user_name has not accepted the task :task_title on :date for the project :project_title led by :leader',
    'user_mark_task_complete_project' => ':user_name marked the task :task_title as completed for the project :project_title led by :leader',
    'user_mark_task_not_complete_project' => ':user_name marked the task :task_title as not completed for the project :project_title led by :leader',
    //Task
    'create_task' => ':company_name assigned you a task :task_title on :date in the store :store_name',
    'edit_task' => ':company_name changed the task to :task_title on :date in the store :store_name',
    'user_accept_task' => ':user_name accepted the task :task_title on :date :store_name',
    'user_decline_accept_task' => ':user_name has not accepted the task :task_title on :date :store_name',
    'user_mark_task_not_complete' => ':user_name marked the task :task_title as not completed',
    //Roster
    'user_cancel_after_accept' => ':employee_name has canceled the roster on :date from :start_hour to :finish_hour in the store :store_name',
    //not response
    'not_response_roster' => 'You did not respond to the roster invitation for today on time',
    'not_response_event' => 'You did not respond to the :event_title invitation today on time',
    'not_response_task' => 'You did not respond to the task :task_title invitation on time at the :store_name',
    'not_response_project' => 'You did not respond to the task :task_title invitation on time from the project :project_title led by :leader',

    'notify_title' => 'You have a new notification',
    'send_creator_when_not_response_task' => ':user did not respond to the task :task_title invitation by the start time',
    'send_creator_when_not_response_project' => ':user did not respond to the task :task_title" invitation by the start time for the project :project_title led by :leader',
];
