# Database

## users

Laravel default.

---

## conversations

Represents one conversation between exactly two users.

Columns

- id
- user_one_id
- user_two_id
- timestamps

Rules

- user_one_id < user_two_id
- One pair = One conversation

---

## messages

Columns

- id
- conversation_id
- sender_id
- body
- timestamps

Rules

- One conversation has many messages.
- One user can send many messages.
