# Telegram Bot Integration & 2FA Password Reset Checklist

**Project:** Tap & Track (Tumpat Solutions Staff & Operations Portal)  
**Document Created:** 14 September 2026  
**Last Updated:** 14 September 2026  
**Status:** In Progress / Ready for Implementation  

---

## 1. Executive Summary & Objective

The primary objective of this integration is to introduce a **free, robust, zero-cost Two-Factor Authentication (2FA) One-Time Password (OTP) verification channel** for self-service password resets via a dedicated Telegram Bot (`@TapAndTrackBot`). 

Because *Tap & Track* authenticates staff using their registered Malaysian mobile number (`users.phone`), Telegram provides an instantaneous, cost-free messaging pipeline without the recurring costs of SMS gateways (saving RM 0.15 - RM 0.30 per SMS in Malaysia) or the complex business verification requirements of the WhatsApp Cloud API.

Once linked, the same bot connection can be repurposed for real-time system alerts (shift reminders, geofence check-in confirmations, and leave request approvals).

---

## 2. Master Implementation Checklist

### Phase 1: Bot Registration & Environment Configuration
- [ ] **1.1** Register new bot with `@BotFather` on Telegram:
  - Command: `/newbot`
  - Name: `Tap & Track Security`
  - Username: e.g. `tap_and_track_bot`
  - Store generated API Token securely.
- [ ] **1.2** Update Environment Variables in `.env` and `.env.example`:
  ```env
  TELEGRAM_BOT_TOKEN=your_bot_token_here
  TELEGRAM_BOT_USERNAME=tap_and_track_bot
  ```
- [ ] **1.3** Register Telegram configuration in `config/services.php`:
  ```php
  'telegram' => [
      'bot_token' => env('TELEGRAM_BOT_TOKEN'),
      'bot_username' => env('TELEGRAM_BOT_USERNAME'),
  ],
  ```

---

### Phase 2: Database Schema & User Model Enhancements
- [ ] **2.1** Create database migration:
  `php artisan make:migration add_telegram_fields_to_users_table`
  - Add `telegram_chat_id` (`string`, `nullable`, `index`).
  - Add `telegram_linked_at` (`timestamp`, `nullable`).
- [ ] **2.2** Update `app/Models/User.php`:
  - Add `telegram_chat_id` and `telegram_linked_at` to `$fillable`.
  - Add `'telegram_linked_at' => 'datetime'` to `$casts`.
  - Add helper methods:
    - `hasTelegramLinked(): bool`
    - `unlinkTelegram(): void`
- [ ] **2.3** Add Telegram Status Indicators in Filament:
  - Admin `UserResource`: Display linked badge (`heroicon-o-chat-bubble-left-right`) and Chat ID.
  - Staff `StaffUserResource`: Allow supervisors to inspect subordinate Telegram connectivity.

---

### Phase 3: Telegram Service Layer
- [ ] **3.1** Implement `app/Services/TelegramService.php`:
  - `sendMessage(string|int $chatId, string $message, string $parseMode = 'HTML'): bool`
  - `sendPasswordResetOtp(User $user, string $otp, int $ttlMinutes = 5): bool`
  - `sendShiftReminder(User $user, string $shiftDetails): bool`
  - `sendLeaveNotification(User $user, string $title, string $message): bool`
  - `generateDeepLink(User $user): string` (generates `https://t.me/<bot>?start=<token>`)
- [ ] **3.2** Resilient Error Handling & Logging:
  - Catch API connection timeouts gracefully using Laravel `Http::timeout(5)`.
  - Log failures to Laravel log without disrupting UI execution.

---

### Phase 4: Account Linking Mechanism
- [ ] **4.1** Deep Link Generation:
  - Secure signed or cached token (TTL: 15 minutes) mapped to `user_id`.
  - User clicks **"Connect Telegram"** in their portal profile or header to launch Telegram app.
- [ ] **4.2** Telegram Webhook / Polling Handler (`app/Http/Controllers/TelegramWebhookController.php`):
  - Listens for `/start <token>` command.
  - Resolves token $\rightarrow$ updates `telegram_chat_id` and `telegram_linked_at` on matching `User`.
  - Sends immediate Telegram welcome confirmation:
    > *"🎉 Success! Your Telegram account has been linked to Tap & Track. You will now receive security OTPs and shift notifications here."*
- [ ] **4.3** In-App Profile Status:
  - Display "Telegram Connected" with an option to disconnect or reconnect if the user changes Telegram accounts.

---

### Phase 5: 2FA Password Reset Workflow
- [ ] **5.1** Add "Forgot Password?" entry point:
  - Insert clean anchor link on `resources/views/auth/login.blade.php` right beside the "Remember Me" checkbox.
- [ ] **5.2** Implement `app/Http/Controllers/PasswordResetController.php`:
  - `showForgotForm()`: Renders phone input view.
  - `sendOtp(Request $request)`:
    - Validate mobile format (`/^01[0-9]{8,9}$/`).
    - Verify user exists and is active.
    - Check if `hasTelegramLinked()` is true. (If false, show clear prompt: *"No Telegram account linked. Please contact your manager or administrator."*)
    - Throttle OTP requests (max 3 requests per 10 minutes per IP/phone).
    - Generate secure 6-digit numeric OTP (`random_int(100000, 999999)`).
    - Store hashed OTP in `password_reset_tokens` table with 5-minute expiration.
    - Dispatch message via `TelegramService`.
    - Redirect to OTP verification view with phone in encrypted session.
  - `showVerifyForm()`: Renders OTP entry view with countdown timer.
  - `verifyAndReset(Request $request)`:
    - Validate OTP against hash and verify within 5-minute TTL.
    - Validate new password (`min:8`, confirmed).
    - Update user password via `Hash::make(...)`.
    - Invalidate OTP entry.
    - Redirect to `/login` with success alert: *"Password updated successfully. Please sign in."*
- [ ] **5.3** Create Frontend Views:
  - `resources/views/auth/forgot-password.blade.php`: Phone entry form matching existing portal branding.
  - `resources/views/auth/reset-password-otp.blade.php`: Modern OTP input, password & confirmation fields.

---

### Phase 6: Multi-Channel Alerts Expansion
- [ ] **6.1** Hook into `AppNotificationService`:
  - When a leave request is approved or rejected, dispatch an alert via Telegram in addition to the Filament in-app bell notification.
  - When an unassigned shift or schedule is published, send Telegram shift reminder.

---

### Phase 7: Verification & Automated Test Suite
- [ ] **7.1** Dedicated Feature Tests (`tests/Feature/TelegramPasswordResetTest.php`):
  - Test OTP request with valid phone and linked Telegram (asserts Telegram API was called with 6-digit code).
  - Test OTP request with unlinked Telegram (asserts validation error message).
  - Test OTP throttling (asserts 429 Too Many Requests on rapid attempts).
  - Test OTP expiration (asserts failure if verified after 5 minutes).
  - Test invalid OTP rejection.
  - Test successful reset and subsequent login with new password.
- [ ] **7.2** Dedicated Unit Tests (`tests/Unit/TelegramServiceTest.php`):
  - Mock `Http::fake()` to verify correct API URLs and payload formats.
- [ ] **7.3** Full Regression Test:
  - Run `php artisan test` to confirm all 114+ existing tests pass (100% green).

---

### Phase 8: Documentation Synchronization
- [ ] **8.1** `README.md`: Update setup instructions with Telegram bot environment variables.
- [ ] **8.2** `docs/active_docs/SRS.md`: Document Functional Requirement for 2FA Telegram Password Reset.
- [ ] **8.3** `docs/active_docs/User_Guide.md`: Add step-by-step instructions for employees on linking Telegram and resetting passwords.
- [ ] **8.4** `docs/inactive_docs/PROJECT_REPORT_SUMMARY.md`: Add Telegram integration to technical innovations summary.
- [ ] **8.5** `docs/inactive_docs/FYP2_REPORT_CURRENT.md`: Update Chapter 4 (Implementation) and Chapter 5 (Testing & Security Verification) with sequence diagrams and screenshots.

---

## 3. Architecture & Data Flow

```
[Staff Member]
      │
      │ 1. Clicks "Forgot Password" on /login
      ▼
[Forgot Password Form] ──> Enter Phone: 0123456789
      │
      │ 2. Submit Phone
      ▼
[PasswordResetController]
      │
      ├──> User exists & has telegram_chat_id?
      │      ├── [NO]  ──> Return "Telegram not linked, contact admin"
      │      └── [YES] ──> Generate 6-digit OTP (e.g. 748192)
      │
      ├──> Hash & store OTP in database (5-minute TTL)
      │
      ▼
[TelegramService] ──> HTTP POST api.telegram.org/bot<TOKEN>/sendMessage
      │
      ▼
[Telegram App] ──> "🔐 Tap & Track: Your password reset code is 748192"
      │
      │ 3. User enters 748192 + New Password
      ▼
[Verify OTP Page] ──> POST /forgot-password/reset
      │
      ▼
[PasswordResetController]
      │
      ├──> Verify OTP validity & expiration
      ├──> Update User password (Hash::make)
      └──> Delete OTP token
      │
      ▼
[Login Screen] <── Redirect with "Password updated successfully!"
```
