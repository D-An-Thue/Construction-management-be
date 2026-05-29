# Huong dan test chat Reverb bang Postman

Tai lieu nay dung de test cac API chat va broadcast auth cua repo `finance.laravel` bang Postman.

## 1. Dieu kien truoc khi test

- Da chay migration:

```bash
php artisan migrate
```

- Da chay API server:

```bash
php artisan serve
```

- Da chay Reverb server neu muon test realtime:

```bash
php artisan reverb:start
```

Luu y:

- Neu dang dung Docker trong repo nay, port `8080` hien co the bi container `phpmyadmin` chiem. Can giai phong port hoac doi mapping truoc khi test realtime.
- API chat dung JWT custom cua repo. Tat ca route `/api/chat/*` va `/api/broadcasting/auth` deu can `Authorization: Bearer <token>`.

## 2. Tao Postman environment

Tao environment, them cac bien sau:

| Variable | Vi du |
| --- | --- |
| `BASE_URL` | `http://127.0.0.1:8000` |
| `TOKEN_A` | de trong luc dau |
| `TOKEN_B` | de trong luc dau |
| `USER_A_ID` | de trong luc dau |
| `USER_B_ID` | de trong luc dau |
| `GROUP_ID` | id group can test |
| `RECIPIENT_PERSON_ID` | person id cua nguoi nhan direct chat |
| `CONVERSATION_ID` | de trong luc dau |
| `MESSAGE_ID` | de trong luc dau |
| `SOCKET_ID` | de trong luc dau |
| `REVERB_APP_KEY` | `finance-chat-key` |
| `REVERB_WS_URL` | `ws://127.0.0.1:8080/app/{{REVERB_APP_KEY}}?protocol=7&client=js&version=8.4.0&flash=false` |

## 3. Dang nhap va luu token

### 3.1. Dang nhap user A

`POST {{BASE_URL}}/api/authentications/login`

Body JSON:

```json
{
  "email": "admin@gmail.com",
  "password": "A@a1234567"
}
```

Script trong tab `Tests`:

```javascript
const data = pm.response.json();
pm.environment.set("TOKEN_A", data.token);
pm.environment.set("USER_A_ID", data.userId);
```

### 3.2. Dang nhap user B

Dung request login thu 2 voi account khac.
```json
{
  "email": "op3477662@gmail.com",
  "password": "A@a1234567"
}
```
Script:

```javascript
const data = pm.response.json();
pm.environment.set("TOKEN_B", data.token);
pm.environment.set("USER_B_ID", data.userId);
pm.environment.set("RECIPIENT_PERSON_ID", data.userId);
```

## 4. Header mac dinh cho request chat

Voi user A:

```http
Authorization: Bearer {{TOKEN_A}}
Accept: application/json
Content-Type: application/json
```

Voi user B, doi `TOKEN_A` thanh `TOKEN_B`.

## 5. Flow test group chat

### 5.1. Tao hoac lay group conversation

`POST {{BASE_URL}}/api/chat/conversations/group`

Body:

```json
{
  "groupId": {{GROUP_ID}}
}
```

Script:

```javascript
const data = pm.response.json();
pm.environment.set("CONVERSATION_ID", data.id);
```

Ky vong:

- `200 OK`
- `type = 1`
- `groupId = {{GROUP_ID}}`

### 5.2. Lay danh sach conversation

`GET {{BASE_URL}}/api/chat/conversations`

Ky vong:

- tra ve mang conversation
- conversation vua tao co `id = {{CONVERSATION_ID}}`

### 5.3. Gui message

`POST {{BASE_URL}}/api/chat/conversations/{{CONVERSATION_ID}}/messages`

Body:

```json
{
  "body": "Xin chao tu Postman",
  "replyToMessageId": null
}
```

Script:

```javascript
const data = pm.response.json();
pm.environment.set("MESSAGE_ID", data.id);
```

Ky vong:

- `200 OK`
- `senderPersonId = {{USER_A_ID}}`
- `body = "Xin chao tu Postman"`

### 5.4. Lay lich su message

`GET {{BASE_URL}}/api/chat/conversations/{{CONVERSATION_ID}}/messages?limit=30`

Ky vong:

- tra ve mang message
- co message `id = {{MESSAGE_ID}}`

### 5.5. Mark read

`POST {{BASE_URL}}/api/chat/conversations/{{CONVERSATION_ID}}/read`

Body:

```json
{
  "messageId": {{MESSAGE_ID}}
}
```

Ky vong:

- `200 OK`
- `conversationId = {{CONVERSATION_ID}}`
- `messageId = {{MESSAGE_ID}}`

### 5.6. Typing

`POST {{BASE_URL}}/api/chat/conversations/{{CONVERSATION_ID}}/typing`

Body:

```json
{
  "isTyping": true
}
```

Ky vong:

- `200 OK`
- `isTyping = true`

## 6. Flow test direct chat

### 6.1. Tao hoac lay direct conversation

`POST {{BASE_URL}}/api/chat/conversations/direct`

Body:

```json
{
  "recipientPersonId": {{RECIPIENT_PERSON_ID}}
}
```

Script:

```javascript
const data = pm.response.json();
pm.environment.set("CONVERSATION_ID", data.id);
```

Ky vong:

- `200 OK`
- `type = 2`

### 6.2. Goi lai request tren lan 2

Ky vong:

- van `200 OK`
- `id` giong lan dau

## 7. Sua va xoa message

### 7.1. Sua message

`PUT {{BASE_URL}}/api/chat/messages/{{MESSAGE_ID}}`

Body:

```json
{
  "body": "Noi dung da sua tu Postman"
}
```

Ky vong:

- `200 OK`
- `id = {{MESSAGE_ID}}`
- `body = "Noi dung da sua tu Postman"`

### 7.2. Xoa message

`DELETE {{BASE_URL}}/api/chat/messages/{{MESSAGE_ID}}`

Ky vong:

- `200 OK`
- body tra ve `true`

## 8. Test phan quyen

### 8.1. User khong thuoc conversation

Lay `TOKEN_B` cua user khong thuoc group, goi:

`GET {{BASE_URL}}/api/chat/conversations/{{CONVERSATION_ID}}/messages`

Ky vong:

- `403 Forbidden`

### 8.2. Broadcast auth khong co token

`POST {{BASE_URL}}/api/broadcasting/auth`

Body `x-www-form-urlencoded`:

| key | value |
| --- | --- |
| `socket_id` | `1234.5678` |
| `channel_name` | `private-chat.user.{{USER_A_ID}}` |

Ky vong:

- `401 Unauthenticated`

### 8.3. Broadcast auth private user channel

`POST {{BASE_URL}}/api/broadcasting/auth`

Headers:

```http
Authorization: Bearer {{TOKEN_A}}
Accept: application/json
Content-Type: application/x-www-form-urlencoded
```

Body `x-www-form-urlencoded`:

| key | value |
| --- | --- |
| `socket_id` | `1234.5678` |
| `channel_name` | `private-chat.user.{{USER_A_ID}}` |

Ky vong:

- `200 OK`
- response co field `auth`

### 8.4. Broadcast auth presence conversation channel

Body `x-www-form-urlencoded`:

| key | value |
| --- | --- |
| `socket_id` | `1234.5678` |
| `channel_name` | `presence-chat.conversation.{{CONVERSATION_ID}}` |

Ky vong:

- `200 OK`
- response co `auth`
- response co `channel_data`

## 9. Test realtime bang Postman WebSocket

Phan nay la optional. Dung khi muon nhin event Reverb ngay trong Postman.

### 9.1. Mo WebSocket request

Tao `New -> WebSocket Request`, URL:

```txt
{{REVERB_WS_URL}}
```

Ket noi thanh cong se nhan duoc event `pusher:connection_established`.

Lay `socket_id` tu payload tra ve va set vao environment `SOCKET_ID`.

### 9.2. Auth channel bang HTTP

Goi:

`POST {{BASE_URL}}/api/broadcasting/auth`

Headers:

```http
Authorization: Bearer {{TOKEN_A}}
Accept: application/json
Content-Type: application/x-www-form-urlencoded
```

Body:

| key | value |
| --- | --- |
| `socket_id` | `{{SOCKET_ID}}` |
| `channel_name` | `presence-chat.conversation.{{CONVERSATION_ID}}` |

Response se co:

- `auth`
- `channel_data`

### 9.3. Subscribe channel trong WebSocket

Gui message sau trong WebSocket tab:

```json
{
  "event": "pusher:subscribe",
  "data": {
    "channel": "presence-chat.conversation.{{CONVERSATION_ID}}",
    "auth": "<gia tri auth tu API /broadcasting/auth>",
    "channel_data": "<gia tri channel_data tu API /broadcasting/auth>"
  }
}
```

Neu subscribe private user channel:

```json
{
  "event": "pusher:subscribe",
  "data": {
    "channel": "private-chat.user.{{USER_A_ID}}",
    "auth": "<gia tri auth tu API /broadcasting/auth>"
  }
}
```

### 9.4. Trigger event bang HTTP request

Sau khi subscribe xong, goi cac API:

- `POST /api/chat/conversations/{id}/messages`
- `PUT /api/chat/messages/{messageId}`
- `DELETE /api/chat/messages/{messageId}`
- `POST /api/chat/conversations/{id}/read`
- `POST /api/chat/conversations/{id}/typing`

Ky vong tren WebSocket:

- `message.sent`
- `message.updated`
- `message.deleted`
- `conversation.read`
- `conversation.typing`

Luu y:

- Trong Echo o frontend, event custom duoc nghe voi prefix dau cham, vi du `.message.sent`
- Trong raw WebSocket/Pusher protocol cua Postman, truong `event` se la `message.sent`, khong co dau cham dau

## 10. Goi y folder Postman

Nen tach collection thanh cac folder:

1. `Auth`
2. `Chat - Group`
3. `Chat - Direct`
4. `Chat - Message`
5. `Broadcast Auth`
6. `WebSocket Reverb`

## 11. Loi thuong gap

### `401 Unauthenticated`

- Token sai
- Quen header `Authorization`
- Token het han

### `403 Forbidden`

- User khong thuoc group conversation
- User khong phai participant cua direct conversation
- User dang auth private channel cua nguoi khac

### Khong nhan duoc event realtime

- Chua chay `php artisan reverb:start`
- Subscribe sai channel
- `socket_id` dung trong `/api/broadcasting/auth` khong khop ket noi WebSocket hien tai
- Reverb dang bi xung dot port `8080`

## 12. Thu tu test de xac nhan nhanh

1. Login user A
2. Login user B
3. Tao group/direct conversation
4. Gui message
5. Lay message history
6. Mark read
7. Typing
8. Update message
9. Delete message
10. Test `/api/broadcasting/auth`
11. Neu can, mo WebSocket Postman de nhin event realtime
