<h2>{{ trans('common.delete_user_email_message', ['name' => $client->first_name,'id' => $client->id],'en') }}</h2>

<ul>
    <li>ID: {{$client->id}}</li>
    <li>Name: {{$client->first_name}}</li>
    <li>EMAIL: {{$client->email}}</li>
    <li>Timestamp: {{$timestamp}}</li>
    <li>Reason: {{$reason}}</li>
</ul>
