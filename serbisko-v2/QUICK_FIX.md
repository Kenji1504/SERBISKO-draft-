# ⚡ QUICK FIX GUIDE - Enrollment Form Filler

## The Problem
The WebDriver check endpoint (`/check-webdriver`) times out with "Win32 application error"  
→ This is a Chrome/ChromeDriver architecture mismatch (32-bit vs 64-bit)

## The Solution (3 Steps)

### Step 1: Clear Cache
```powershell
Remove-Item "$env:APPDATA\.wdm" -Recurse -Force
```

### Step 2: Restart Service
```bash
cd python_services
py enrollment_form_filler.py
```

### Step 3: Test in Browser
1. Go to: **http://127.0.0.1:8887/admin/students/profile/00634655172**
2. Click blue **"Use Profile"** button
3. Watch what happens:
   - ✅ Chrome opens → **SUCCESS** (continue to test form filling)
   - ⚠ Chrome opens but blank or error → Check LIS credentials
   - ✗ Chrome doesn't open → Try step 1-2 again
   - ✗ Still fails → Check Flask logs for specific error

## What You Should See

**Flask Terminal Output** (should show):
```
[HTTP] POST http://localhost:5002/fill-enrollment
[DATA] Received student data: LRN: 00634655172
[ACTION] Starting Chrome browser in background thread...
[SUCCESS] Form filler thread started!
```

**Chrome Window**:
1. LoginIS form loads
2. Auto-fills with: depedsample@gmail.com / deped123
3. Auto-clicks login button
4. Navigates to enrollment form
5. Pre-fills student info from Serbisko

## If Still Broken

**Check Flask logs for this exact error:**
```
error:  OSError(8, '%1 is not a valid Win32 application', None, 193, None)
```

**Then try:**
```powershell
# Option 1: Skip cache on next run
$env:WDM_SKIP_CACHE = '1'
py enrollment_form_filler.py

# Option 2: Download correct ChromeDriver manually
# (Go to: https://chromedriver.chromium.org)
# Get version matching your Chrome version
# Place in python_services/chromedriver.exe
# Edit enrollment_form_filler.py line 122:
#   service = Service(r"python_services/chromedriver.exe")
```

## Files Changed
- ✅ Syntax errors fixed
- ✅ Auto-detection added for Chrome binary
- ✅ Better error logging added
- ✅ Graceful fallbacks implemented

**All ready to test!** 🚀
