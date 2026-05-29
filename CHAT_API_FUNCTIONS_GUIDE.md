# Huong dan chuc nang Chat API

Tai lieu nay mo ta chuc nang, du lieu vao/ra, va rule nghiep vu cua cac API chat trong repo `finance.laravel`.

## 1. Tong quan

Tat ca API ben duoi deu nam duoi prefix:

```text
/api/chat/*
```

Tat ca request deu can header:

```http
Authorization: Bearer <jwt-token>
Accept: application/json
```

## 2. Quy uoc du lieu

### 2.1. Conversation type

- `1`: group conversation
- `2`: direct conversation

### 2.2. Message type

- `1`: text
- `2`: file
- `3`: system

### 2.3. Kieu response

- Response duoc tra ve theo `BaseApiController::jsonResponse`
- Key trong JSON duoc dua ve dang `camelCase`
- Cac truong thoi gian duoc tra ve dang chuoi ngay gio

## 3. Rule truy cap

- User phai co JWT hop le
- Group conversation duoc phep truy cap khi user la thanh vien cua group
- Direct conversation duoc phep truy cap khi user la participant cua conversation
- `edit message` va `delete message` chi cho phep chinh nguoi gui message
- `typing` chi broadcast trang thai, khong luu xuong DB
- `mark read` cap nhat trang thai da doc tren participant cua conversation

## 4. Mo ta tung endpoint

### 4.1. GET /api/chat/conversations

Muc dich:

- Lay danh sach conversation ma user hien tai duoc phep truy cap
- Gom ca group conversation va direct conversation
- Sap xep uu tien conversation co message moi nhat

Response moi item gom:

- `id`
- `type`
- `groupId`
- `title`
- `avatarUrl`
- `lastMessage`
- `lastMessageAt`
- `unreadCount`

Y nghia:

- Voi group chat, `title` lay tu `GroupName`
- Voi direct chat, `title` lay tu ten nguoi con lai
- `unreadCount` la so message chua doc cua user hien tai

Response mau:

```json
[
  {
    "id": 12,
    "type": 1,
    "groupId": 5,
    "title": "Team Backend",
    "avatarUrl": null,
    "lastMessage": {
      "id": 101,
      "body": "Xin chao",
      "senderPersonId": 7,
      "createdAt": "2026-05-27 10:30:00"
    },
    "lastMessageAt": "2026-05-27 10:30:00",
    "unreadCount": 3
  }
]
```

### 4.2. POST /api/chat/conversations/group

Muc dich:

- Tao group conversation neu chua co
- Neu da co conversation cua group thi tra lai conversation hien tai
- Dong bo participant cua conversation theo thanh vien group

Body:

```json
{
  "groupId": 5
}
```

Rule:

- User phai la thanh vien cua group
- 1 group chi co 1 conversation dung chung

Response:

- Tra ve conversation summary cung format voi API danh sach conversation

Truong hop dung:

- Frontend mo man hinh chat cho 1 group
- Backend tu tao conversation neu group chat chua ton tai

### 4.3. POST /api/chat/conversations/direct

Muc dich:

- Tao direct conversation giua user hien tai va 1 user khac neu chua co
- Neu da ton tai direct conversation thi tai su dung conversation do

Body:

```json
{
  "recipientPersonId": 9
}
```

Rule:

- `recipientPersonId` phai ton tai
- Conversation direct duoc xac dinh theo cap 2 user, khong tao trung

Response:

- Tra ve conversation summary cung format voi API danh sach conversation

Truong hop dung:

- Bam nut "nhan tin rieng" cho 1 nguoi dung
- Lay nhanh id conversation direct de tiep tuc gui message

### 4.4. GET /api/chat/conversations/{conversationId}/messages

Muc dich:

- Lay lich su message cua conversation
- Ho tro tai them du lieu cu theo `beforeId`

Query params:

- `beforeId`: optional, chi lay message co `id < beforeId`
- `limit`: optional, mac dinh `30`, toi da `100`

Vi du:

```text
GET /api/chat/conversations/12/messages?beforeId=101&limit=20
```

Rule:

- User phai co quyen truy cap conversation
- Neu khong co `beforeId` thi lay trang dau tien
- Du lieu duoc tra ve theo thu tu tang dan cua `id` trong trang hien tai

Response moi item gom:

- `id`
- `conversationId`
- `senderPersonId`
- `messageType`
- `body`
- `metadata`
- `replyToMessageId`
- `editedAt`
- `createdAt`
- `updatedAt`
- `sender`
- `replyTo`

Response mau:

```json
[
  {
    "id": 101,
    "conversationId": 12,
    "senderPersonId": 7,
    "messageType": 1,
    "body": "Xin chao",
    "metadata": null,
    "replyToMessageId": null,
    "editedAt": null,
    "createdAt": "2026-05-27 10:30:00",
    "updatedAt": null,
    "sender": {
      "id": 7,
      "name": "Admin",
      "avatarUrl": null
    },
    "replyTo": null
  }
]
```

### 4.5. POST /api/chat/conversations/{conversationId}/messages

Muc dich:

- Gui message moi vao conversation
- Cap nhat `lastMessageId` va `lastMessageAt` cho conversation
- Broadcast event `message.sent` sau khi ghi DB thanh cong

Body:

```json
{
  "body": "Hello from API",
  "replyToMessageId": null,
  "metadata": null,
  "messageType": 1
}
```

Rule:

- User phai co quyen truy cap conversation
- Neu la group conversation, user can la thanh vien group
- Neu co `replyToMessageId` thi message duoc reply phai thuoc cung conversation

Response:

- Tra ve object message vua tao

Truong hop dung:

- Gui text message
- Gui file message neu he thong tuong lai luu metadata file trong `metadata`
- Gui reply message

### 4.6. POST /api/chat/conversations/{conversationId}/read

Muc dich:

- Danh dau user da doc den 1 message cu the
- Cap nhat `LastReadMessageId` va `LastReadAt` tren participant
- Broadcast event `conversation.read`

Body:

```json
{
  "messageId": 101
}
```

Rule:

- User phai co quyen truy cap conversation
- `messageId` phai thuoc dung conversation

Response:

```json
{
  "conversationId": 12,
  "messageId": 101,
  "readAt": "2026-05-27 10:35:00"
}
```

Truong hop dung:

- Frontend mo conversation va cap nhat vi tri da doc cuoi cung
- Tinh `unreadCount` o danh sach conversation

### 4.7. POST /api/chat/conversations/{conversationId}/typing

Muc dich:

- Phat su kien user dang go phim trong conversation
- Broadcast event `conversation.typing`

Body:

```json
{
  "isTyping": true
}
```

Rule:

- User phai co quyen truy cap conversation
- Neu khong gui `isTyping`, backend mac dinh la `true`
- API nay khong luu DB

Response:

```json
{
  "conversationId": 12,
  "personId": 7,
  "isTyping": true
}
```

Truong hop dung:

- Hien thi "nguoi kia dang nhap..."
- Gui `false` khi user dung go

### 4.8. PUT /api/chat/messages/{messageId}

Muc dich:

- Sua noi dung message da gui
- Cap nhat `EditedAt`
- Broadcast event `message.updated`

Body:

```json
{
  "body": "Noi dung da sua"
}
```

Rule:

- User phai co quyen truy cap conversation chua message
- Chi nguoi gui message moi duoc sua

Response:

- Tra ve object message sau khi sua

Truong hop dung:

- Sua loi chinh ta
- Cap nhat noi dung truoc khi cac user khac doc

### 4.9. DELETE /api/chat/messages/{messageId}

Muc dich:

- Xoa mem message
- Cap nhat lai `lastMessageId` va `lastMessageAt` cua conversation neu can
- Broadcast event `message.deleted`

Rule:

- User phai co quyen truy cap conversation chua message
- Chi nguoi gui message moi duoc xoa
- Xoa theo co che soft delete, khong xoa vat ly record khoi DB

Response:

```json
true
```

Truong hop dung:

- Thu hoi message vua gui
- An message khong con muon hien thi

## 5. Loi thuong gap

### 401 Unauthorized

Xay ra khi:

- Khong gui JWT
- JWT khong hop le

### 403 Forbidden

Xay ra khi:

- User khong thuoc group conversation
- User khong phai participant cua direct conversation
- User co truy cap conversation nhung khong phai nguoi gui message de sua/xoa

### 404 Not Found

Xay ra khi:

- `conversationId` khong ton tai
- `messageId` khong ton tai
- `recipientPersonId` hoac `groupId` khong ton tai

### 422 Unprocessable Entity

Xay ra khi payload sai validate, vi du:

- thieu `groupId`
- thieu `recipientPersonId`
- thieu `body`
- `limit` vuot qua range hop le

## 6. Thu tu tich hop goi y cho frontend

1. Login lay JWT
2. Goi `GET /api/chat/conversations` de ve danh sach
3. Neu mo group chat: goi `POST /api/chat/conversations/group`
4. Neu mo direct chat: goi `POST /api/chat/conversations/direct`
5. Goi `GET /api/chat/conversations/{conversationId}/messages`
6. Goi `POST /api/chat/conversations/{conversationId}/messages` khi gui tin nhan
7. Goi `POST /api/chat/conversations/{conversationId}/read` khi doc message
8. Goi `POST /api/chat/conversations/{conversationId}/typing` khi nhap lieu
9. Goi `PUT /api/chat/messages/{messageId}` hoac `DELETE /api/chat/messages/{messageId}` khi can sua/xoa

## 7. Ghi chu cho test

- File test Postman tham khao: `CHAT_REVERB_POSTMAN_GUIDE.md`
- Collection Postman da co folder `Chat` trong `routes_all_postman.postman_collection.json`
- Neu test realtime qua Reverb trong Docker, can xu ly xung dot port `8080` truoc
