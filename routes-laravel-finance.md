# Danh sách routes theo nhóm (`finance.laravel`)

Nguồn: `php artisan route:list --no-ansi`

## 1) System / Web
- `GET|HEAD /` → `routes/web.php:5`
- `GET|HEAD api/health` → `routes/api.php:19`
- `GET|HEAD up` → `ApplicationBuilder.php:221`

## 2) Authentication
- `POST api/authentications/login` → `Api\AuthController@login`
- `POST api/authentications/register` → `Api\AuthController@register`
- `POST api/authentications/forgot-pasword` → `Api\AuthController@forgotPassword`
- `GET|HEAD api/authentications/me` → `Api\AuthController@me`

## 3) App Settings
- `GET|HEAD api/appsettings` → `Api\AppSettingController@show`
- `POST api/appsettings` → `Api\AppSettingController@store`
- `PUT api/appsettings` → `Api\AppSettingController@update`
- `GET|HEAD api/appsettings/public` → `Api\AppSettingController@public`

## 4) Dashboard
- `GET|HEAD api/dashboard` → `Api\DashboardController@summary`
- `GET|HEAD api/dashboard/tasks` → `Api\DashboardController@taskStats`
- `GET|HEAD api/dashboard/tickets` → `Api\DashboardController@ticketStats`

## 5) Groups
- `GET|HEAD api/groups/group` → `Api\GroupController@index`
- `GET|HEAD api/groups/group/{idGroups}` → `Api\GroupController@show`
- `POST api/groups/group` → `Api\GroupController@store`
- `PUT api/groups/group` → `Api\GroupController@update`
- `DELETE api/groups/group` → `Api\GroupController@destroy`

## 6) Person Groups
- `POST api/person/groups` → `Api\PersonGroupController@store`
- `PUT api/person/groups` → `Api\PersonGroupController@update`
- `DELETE api/person/groups` → `Api\PersonGroupController@destroy`
- `PUT api/person/groups/admin` → `Api\PersonGroupController@setAdmin`
- `PUT api/person/groups/status` → `Api\PersonGroupController@setStatus`

## 7) Persons
- `GET|HEAD api/persons/person` → `Api\PersonController@index`
- `GET|HEAD api/persons/person/{idPerson}` → `Api\PersonController@show`
- `POST api/persons/person` → `Api\PersonController@store`
- `PUT api/persons/person` → `Api\PersonController@update`
- `DELETE api/persons/person` → `Api\PersonController@destroy`

## 8) Products
- `GET|HEAD api/products/product` → `Api\ProductController@index`
- `GET|HEAD api/products/product/{id}` → `Api\ProductController@show`
- `POST api/products/product` → `Api\ProductController@store`
- `PUT api/products/product` → `Api\ProductController@update`
- `DELETE api/products/product` → `Api\ProductController@destroy`
- `POST api/products/product/import` → `Api\ProductController@import`
- `GET|HEAD api/products/product/export` → `Api\ProductController@export`

## 9) Roles
- `GET|HEAD api/roles` → `Api\RoleController@index`
- `GET|HEAD api/roles/{roleId}` → `Api\RoleController@show`
- `GET|HEAD api/roles/me` → `Api\RoleController@me`
- `GET|HEAD api/roles/permissions` → `Api\RoleController@permissions`
- `POST api/roles` → `Api\RoleController@store`
- `PUT api/roles` → `Api\RoleController@update`
- `DELETE api/roles/{roleId}` → `Api\RoleController@destroy`
- `POST api/roles/{roleId}/permissions` → `Api\RoleController@assignPermission`
- `DELETE api/roles/{roleId}/permissions/{permissionId}` → `Api\RoleController@removePermission`
- `POST api/roles/{roleId}/users` → `Api\RoleController@assignUser`
- `DELETE api/roles/{roleId}/users/{personId}` → `Api\RoleController@removeUser`
- `POST api/roles/seed` → `Api\RoleController@seed`

## 10) Tasks
- `GET|HEAD api/tasks/task` → `Api\TaskController@index`
- `GET|HEAD api/tasks/task/{id}/details` → `Api\TaskController@show`
- `POST api/tasks/task` → `Api\TaskController@store`
- `PUT api/tasks/task` → `Api\TaskController@update`
- `DELETE api/tasks/task` → `Api\TaskController@destroy`

### 10.1) Task Comments
- `GET|HEAD api/tasks/task/{taskId}/comments` → `Api\TaskCommentController@index`
- `POST api/tasks/task/{taskId}/comments` → `Api\TaskCommentController@store`
- `PUT api/tasks/task/comments` → `Api\TaskCommentController@update`
- `DELETE api/tasks/task/comments` → `Api\TaskCommentController@destroy`

### 10.2) Sub Tasks
- `GET|HEAD api/tasks/{taskId}/subtasks` → `Api\SubTaskController@index`
- `POST api/tasks/{taskId}/subtasks` → `Api\SubTaskController@store`
- `PUT api/tasks/{taskId}/subtasks` → `Api\SubTaskController@update`
- `DELETE api/tasks/{taskId}/subtasks` → `Api\SubTaskController@destroy`

## 11) Tickets
- `GET|HEAD api/tickets/ticket` → `Api\TicketController@index`
- `GET|HEAD api/tickets/ticket/{id}/details` → `Api\TicketController@show`
- `POST api/tickets/ticket` → `Api\TicketController@store`
- `PUT api/tickets/ticket` → `Api\TicketController@update`
- `PUT api/tickets/approve` → `Api\TicketController@approve`
- `DELETE api/tickets/ticket` → `Api\TicketController@destroy`

## 12) Transactions
- `GET|HEAD api/transactions/transaction/{id}/details` → `Api\TransactionController@show`

## 13) Uploads
- `POST api/Uploads/Upload` → `Api\UploadController@store`

## 14) Storage
- `GET|HEAD storage/{path}` → `storage.local`
- `PUT storage/{path}` → `storage.local.upload`

---
Tổng số routes từ lệnh gốc: **71**.
