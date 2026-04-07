# 🚀 Quick Start Guide: Enrollment Form Filler

## 5-Minute Setup

### Step 1: Install Dependencies (One-time only)

Run this command once in the terminal:

```bash
cd python_services
pip install flask flask-cors selenium webdriver-manager urllib3
```

### Step 2: Start the Service

**Option A - Easy Way (Windows):**
- Double-click: `python_services/start_enrollment_filler.bat`

**Option B - Manual:**
```bash
cd python_services
python enrollment_form_filler.py
```

You should see:
```
Starting Enrollment Form Filler Service on Port 5002...
```

### Step 3: Use in Serbisko

1. Go to: **Admin → Students**
2. Click **View** on any student
3. Click **"Use Profile"** button (top right)
4. Chrome opens with LIS pre-filled form
5. Verify information
6. **Click submit button manually** in LIS

Done! ✓

---

## What Happens When You Click "Use Profile"

```
┌─────────────────┐
│ Student Profile │
│                 │
│ [Use Profile]←──┐
└─────────────────┘ │
                    │ Sends student data
                    ↓
         ┌──────────────────┐
         │ Python Service   │
         │ (Port 5002)      │
         └──────────────────┘
                    │
         Automates  │
                    ↓
         ┌──────────────────┐
         │ LIS Website      │
         │ - Logs in        │
         │ - Opens form     │
         │ - Fills fields   │ ← Admin verifies
         │ - **STOPS** ⏹    │   Admin submits
         └──────────────────┘
```

---

## Troubleshooting

### Issue: "Service not found on port 5002"
→ Make sure the batch file or Python script is running
→ Should see terminal window with service info

### Issue: "Browser doesn't open"
→ Make sure **Google Chrome** is installed (preferred) or Firefox
→ Check terminal for error messages

### Issue: "Form fields not filled"
→ LIS website might have changed
→ Some fields may not auto-fill due to HTML structure
→ You can manually fill the remaining fields

---

## Features

✅ **Auto-fills** student data from Serbisko  
✅ **Opens LIS** browser automatically  
✅ **Logs in** with stored credentials  
✅ **Navigates** to enrollment form  
✅ **Stops before** final submission  
✅ **Allows** admin to verify everything  
✅ **Browser stays open** for manual submission  

❌ Does NOT auto-submit enrollment  
❌ Does NOT close the browser  
❌ Does NOT modify student records  

---

## Port Information

- **Service runs on**: `http://localhost:5002`
- **Serbisko connects to**: `http://localhost:5002/fill-enrollment`
- **Service status**: `http://localhost:5002/status`

If port 5002 is already in use, edit `enrollment_form_filler.py` and change:
```python
app.run(host='0.0.0.0', port=5003, debug=True)
```

Then update Serbisko profile page JavaScript to use port 5003.

---

## Data Sent to LIS

When you click "Use Profile", these student details are automatically filled:

- Name (First, Middle, Last, Extension)
- Date of Birth
- Contact Information
- Gender, Age, Mother Tongue
- Place of Birth
- Grade Level, Track, Cluster
- Academic Status
- School Year

---

## Admin Workflow Example

1. **Login to Serbisko** as Admin
2. **Go to Students** → View List
3. **Find a student** → Click "View"
4. **Click "Use Profile"**
   - Chrome opens automatically
   - LIS form pre-filled with student info
5. **Verify each field** in the LIS form
6. **Correct any errors** manually if needed
7. **Review Academic Information** (Grade, Track, Cluster)
8. **Click "Submit/Enroll"** in LIS browser
9. **Wait for confirmation** in LIS
10. Done! ✓

---

## Security Notes

⚠️ The LIS credentials are stored in `enrollment_form_filler.py`:
```python
CREDS = ("email@example.com", "password123")
```

For production, consider:
- Using environment variables
- Encrypting credentials
- Using a config file
- Restricting file permissions

---

## Stopping the Service

- **Option 1**: Close the terminal window
- **Option 2**: Press `Ctrl + C` in terminal
- **Option 3**: Kill via Task Manager (Python process)

Note: Browsers opened by the service will stay open until you manually close them or the system shuts down.

---

## Next Steps

1. ✓ Install dependencies
2. ✓ Start the service
3. ✓ Try clicking "Use Profile" on a student
4. ✓ Verify form fills correctly
5. ✓ Manually submit in LIS
6. ✓ Repeat for other students

For detailed documentation, see: `python_services/ENROLLMENT_FORM_FILLER_README.md`

---

**Questions?** Check the full README.md file in the python_services folder.
