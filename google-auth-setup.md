# Google Authentication Setup Guide

## 🔧 Supabase Google OAuth Configuration

To enable Google login/signup, you need to configure Google OAuth in your Supabase project:

### Step 1: Create Google OAuth App

1. Go to [Google Cloud Console](https://console.cloud.google.com/)
2. Create a new project or select existing one
3. Enable the Google+ API
4. Go to "Credentials" → "Create Credentials" → "OAuth 2.0 Client IDs"
5. Set application type to "Web application"
6. Add authorized redirect URIs:
   - For development: `https://[your-supabase-project].supabase.co/auth/v1/callback`
   - For production: `https://[your-domain].com/auth/v1/callback`

### Step 2: Configure Supabase

1. Go to your Supabase Dashboard
2. Navigate to Authentication → Providers
3. Enable Google provider
4. Enter your Google OAuth credentials:
   - **Client ID**: From Google Cloud Console
   - **Client Secret**: From Google Cloud Console

### Step 3: Update Database Policies (if needed)

Make sure your RLS policies allow Google authenticated users:

```sql
-- This should already be covered by existing policies
-- but verify in your Supabase dashboard
```

## 🎯 How Google Auth Works in the App

### For New Users (Google Sign Up)
1. User clicks "Sign Up with Google" on `/signup`
2. Redirected to Google OAuth consent screen
3. After approval, returned to app with Google profile
4. User can select role (Guest or Property Owner)
5. Profile created automatically in database

### For Existing Users (Google Sign In)  
1. User clicks "Sign in with Google" on `/login`
2. Redirected to Google OAuth consent screen
3. After approval, signed in automatically
4. Redirected based on user role:
   - **Guests**: `/explore` page
   - **Owners**: `/owner/dashboard`

## 🔐 Authentication Flow

```
User clicks Google button
    ↓
Supabase.auth.signInWithOAuth()
    ↓
Redirect to Google
    ↓
User approves permissions
    ↓
Google redirects to Supabase callback
    ↓
Supabase creates/updates user
    ↓
App receives auth state change
    ↓
User logged in and redirected
```

## 🚨 Troubleshooting

### Common Issues:

1. **"Redirect URI mismatch"**
   - Check that your redirect URI in Google Console matches Supabase exactly
   - Format: `https://[project-id].supabase.co/auth/v1/callback`

2. **"Google Sign-In Failed"**
   - Verify Google provider is enabled in Supabase
   - Check that Client ID and Secret are correct
   - Ensure Google+ API is enabled

3. **User not redirected after login**
   - Check browser console for errors
   - Verify auth state is being handled correctly

### Testing Locally:
- Google OAuth works in development mode
- Use the same Supabase redirect URI for both dev and prod
- Test with different Google accounts

## 📱 Mobile Considerations

- Google OAuth works on mobile browsers
- Consider implementing deep linking for mobile apps
- Test on both iOS Safari and Android Chrome

## 🔒 Security Notes

- Google OAuth uses PKCE flow for security
- User emails are automatically verified
- No passwords stored for Google users
- Can link Google accounts to existing email/password accounts
