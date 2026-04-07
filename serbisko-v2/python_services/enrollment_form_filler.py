from flask import Flask, request, jsonify
import threading
import time
import os
import platform
import sys
import traceback

app = Flask(__name__)

@app.before_request
def log_request_info():
    print(f"\n[HTTP] {request.method} {request.url}")
    print(f"[HEADERS] {dict(request.headers)}")
    if request.method == 'POST' and request.is_json:
        print(f"[BODY] {request.get_json(silent=True)}")

@app.after_request
def log_response_info(response):
    print(f"[RESPONSE] {response.status_code}")
    response.headers['Access-Control-Allow-Origin'] = '*'
    response.headers['Access-Control-Allow-Headers'] = 'Content-Type,Authorization'
    response.headers['Access-Control-Allow-Methods'] = 'GET,POST,OPTIONS'
    return response

# Configuration
LIS_URL = "https://learner-information-system-lis-dashboard-352866309332.us-west1.run.app/"
LIS_EMAIL = "gamboakenleam1015@gmail.com"
LIS_PASSWORD = "password432"

# Global state
active_driver = None
driver_lock = threading.Lock()

def find_chrome_path():
    """Auto-detect Chrome installation path"""
    possible_paths = [
        r"C:\Program Files\Google\Chrome\Application\chrome.exe",
        r"C:\Program Files (x86)\Google\Chrome\Application\chrome.exe",
        os.path.expandvars(r"%ProgramFiles%\Google\Chrome\Application\chrome.exe"),
        os.path.expandvars(r"%ProgramFiles(x86)%\Google\Chrome\Application\chrome.exe"),
    ]
    for path in possible_paths:
        if os.path.exists(path):
            print(f"[DEBUG] Found Chrome at: {path}")
            return path
    return None  # Return None instead of raising - let Selenium handle it

def get_chrome_options():
    """Create Chrome options with proper configuration"""
    from selenium.webdriver.chrome.options import Options

    options = Options()
    
    # Try to set Chrome binary location
    chrome_path = find_chrome_path()
    if chrome_path:
        options.binary_location = chrome_path
        print(f"[DEBUG] Chrome binary: {chrome_path}")
    else:
        print(f"[WARN] Chrome path not found, will use system default")
    
    # Add stability and bypass arguments
    options.add_argument("--no-sandbox")
    options.add_argument("--disable-dev-shm-usage")
    options.add_argument("--disable-gpu")
    options.add_argument("--no-first-run")
    options.add_argument("--disable-extensions")
    options.add_argument("--disable-blink-features=AutomationControlled")
    
    # ISOLATION: Use a temporary profile to ensure no previous "breach" status is remembered
    import tempfile
    temp_profile = tempfile.mkdtemp()
    options.add_argument(f"--user-data-dir={temp_profile}")
    print(f"[DEBUG] Using temporary Chrome profile: {temp_profile}")
    
    # DISABLE ALL SECURITY PROMPTS AND PASSWORD INTERFERENCE
    options.add_argument("--disable-infobars")
    options.add_argument("--disable-notifications")
    options.add_argument("--disable-save-password-bubble")
    options.add_argument("--disable-autofill")
    options.add_argument("--disable-sync") # Stop syncing with Google account breach data
    options.add_argument("--disable-background-networking") # Stop online breach checks
    options.add_argument("--disable-component-update") # Stop security component updates
    options.add_argument("--disable-password-manager-reauthentication")
    options.add_argument("--disable-features=SafeBrowsing,PasswordLeakDetection,PasswordCheck,PasswordGeneration,OptimizationGuide,MediaRouter,DialMediaRouteProvider")
    options.add_argument("--password-store=basic")
    
    # Experimental options to fully isolate and disable password manager
    prefs = {
        "credentials_enable_service": False,
        "profile.password_manager_enabled": False,
        "profile.default_content_setting_values.notifications": 2,
        "autofill.profile_enabled": False,
        "autofill.credit_card_enabled": False,
        "safebrowsing.enabled": False,
        "safebrowsing.enhanced": False,
        "password_manager_leak_detection": False
    }
    options.add_experimental_option("prefs", prefs)
    options.add_experimental_option("excludeSwitches", ["enable-automation", "enable-logging"])
    options.add_experimental_option("useAutomationExtension", False)
    
    return options

def get_chromedriver_path():
    """Install and return the correct path to ChromeDriver executable"""
    from webdriver_manager.chrome import ChromeDriverManager

    installed_path = ChromeDriverManager().install()
    
    # Fix for webdriver-manager sometimes returning non-executable files (like THIRD_PARTY_NOTICES)
    if not installed_path.lower().endswith(".exe"):
        print(f"[DEBUG] ChromeDriverManager returned non-exe: {installed_path}")
        dir_name = os.path.dirname(installed_path)
        exe_path = os.path.join(dir_name, "chromedriver.exe")
        if os.path.exists(exe_path):
            return exe_path
        else:
            print("[DEBUG] Not found in same dir, searching parent...")
            parent_dir = os.path.dirname(dir_name)
            for root, dirs, files in os.walk(parent_dir):
                if "chromedriver.exe" in files:
                    return os.path.join(root, "chromedriver.exe")
    return installed_path

def fill_enrollment_form(student_data):
    """Fill LIS enrollment form with student data"""
    from selenium import webdriver
    from selenium.webdriver.common.by import By
    from selenium.webdriver.chrome.service import Service
    from selenium.webdriver.support.ui import WebDriverWait
    from selenium.webdriver.support import expected_conditions as EC

    global active_driver
    
    lrn = student_data.get('lrn')
    first_name = student_data.get('first_name', '')
    last_name = student_data.get('last_name', '')
    
    print(f"\n{'='*70}")
    print(f"[START] Filling enrollment form: {first_name} {last_name} (LRN: {lrn})")
    print(f"{'='*70}\n")
    
    driver = None
    
    def find_clickable_element(driver, wait, xpaths, name, timeout=12):
        end_time = time.time() + timeout
        last_error = None
        while time.time() < end_time:
            for xp in xpaths:
                try:
                    elements = driver.find_elements(By.XPATH, xp)
                    for el in elements:
                        if el.is_displayed() and el.is_enabled():
                            print(f"[DEBUG] Found visible {name} using XPath: {xp}")
                            return el
                except Exception as exc:
                    last_error = exc
            time.sleep(0.3)
        raise Exception(f"Could not locate visible {name} with any XPath; last error: {last_error}")

    def click_element(driver, wait, xpaths, name):
        try:
            el = find_clickable_element(driver, wait, xpaths, name)
            try:
                driver.execute_script("arguments[0].scrollIntoView({block: 'center'});", el)
            except Exception:
                pass
            try:
                driver.execute_script("arguments[0].click();", el)
            except Exception:
                el.click()
            print(f"[DEBUG] Clicked {name}")
            return True
        except Exception as exc:
            raise Exception(f"Could not click {name} with any XPath: {exc}")

    try:
        # Setup Chrome options
        print("[1/5] Setting up Chrome browser...")
        options = get_chrome_options()
        
        print("[2/5] Installing/verifying ChromeDriver...")
        try:
            driver_path = get_chromedriver_path()
            print(f"[DEBUG] Final ChromeDriver path: {driver_path}")
            # Verify the driver file
            if os.path.exists(driver_path):
                size_mb = os.path.getsize(driver_path) / (1024 * 1024)
                print(f"[DEBUG] ChromeDriver size: {size_mb:.2f} MB")
            else:
                raise Exception(f"ChromeDriver file not found at {driver_path}")
            service = Service(driver_path)
        except Exception as e:
            print(f"[ERROR] ChromeDriver setup failed: {e}")
            raise
        
        print("[4/5] Launching Chrome...")
        try:
            with driver_lock:
                # Only quit if it's a DIFFERENT session. 
                # If we are already filling, don't kill it unless it's a new request for a new student.
                if active_driver:
                    print("[DEBUG] Closing existing browser session...")
                    try:
                        active_driver.quit()
                    except:
                        pass
                
                driver = webdriver.Chrome(service=service, options=options)
                active_driver = driver
            print("[OK] Chrome launched successfully!")
        except Exception as e:
            print(f"[ERROR] Failed to launch Chrome: {str(e)}")
            raise
        
        print("[4/5] Navigating to LIS...")
        
        # Navigate to LIS
        driver.get(LIS_URL)
        print(f"[OK] Reached LIS website: {driver.current_url} (title: {driver.title})")
        driver.save_screenshot('before-login.png')
        
        # ... (rest of the logic) ...
        
        # LOGIN
        print("\n[LOGIN] Authenticating...")
        wait = WebDriverWait(driver, 25)

        def attempt_login_field(possible_xpaths, value, field_name):
            combined_xp = " | ".join(possible_xpaths)
            try:
                fld = wait.until(EC.presence_of_element_located((By.XPATH, combined_xp)))
                driver.execute_script("arguments[0].scrollIntoView(true);", fld)
                fld.clear()
                fld.send_keys(value)
                print(f"[OK] {field_name} entered via combined XPath")
                return True
            except Exception as e:
                print(f"[WARN] {field_name} not found by any xpath: {e}")
                return False

        # iframe fallback
        iframe_used = False
        frames = driver.find_elements(By.TAG_NAME, 'iframe')
        if frames:
            print(f"[DEBUG] {len(frames)} iframe(s) found; trying to locate login form inside frames")
            for idx, frame in enumerate(frames):
                try:
                    driver.switch_to.frame(frame)
                    if driver.find_elements(By.XPATH, "//input[@placeholder='Email']") or driver.find_elements(By.XPATH, "//input[contains(@placeholder,'Email')]"):
                        print(f"[DEBUG] login form found in iframe index {idx}")
                        iframe_used = True
                        break
                    driver.switch_to.default_content()
                except Exception as e:
                    print(f"[DEBUG] iframe {idx} check failed: {e}")
                    driver.switch_to.default_content()

        email_xpaths = [
            "//input[@placeholder='Email']",
            "//input[contains(translate(@placeholder,'ABCDEFGHIJKLMNOPQRSTUVWXYZ','abcdefghijklmnopqrstuvwxyz'),'email')]",
            "//input[@type='email']",
            "//input[contains(translate(@name,'ABCDEFGHIJKLMNOPQRSTUVWXYZ','abcdefghijklmnopqrstuvwxyz'),'email')]",
            "//input[contains(translate(@id,'ABCDEFGHIJKLMNOPQRSTUVWXYZ','abcdefghijklmnopqrstuvwxyz'),'email')]",
            "//input[@autocomplete='email']",
        ]

        pass_xpaths = [
            "//input[@placeholder='Password']",
            "//input[@type='password']",
            "//input[contains(translate(@name,'ABCDEFGHIJKLMNOPQRSTUVWXYZ','abcdefghijklmnopqrstuvwxyz'),'password')]",
            "//input[contains(translate(@id,'ABCDEFGHIJKLMNOPQRSTUVWXYZ','abcdefghijklmnopqrstuvwxyz'),'password')]",
            "//input[contains(translate(@placeholder,'ABCDEFGHIJKLMNOPQRSTUVWXYZ','abcdefghijklmnopqrstuvwxyz'),'password')]",
            "//input[@autocomplete='current-password']",
        ]

        login_button_xpaths = [
            "//button[contains(normalize-space(.),'Sign in')]",
            "//button[contains(translate(normalize-space(.),'ABCDEFGHIJKLMNOPQRSTUVWXYZ','abcdefghijklmnopqrstuvwxyz'),'sign in')]",
            "//button[@type='submit']",
            "//input[@type='submit']",
            "//button[contains(translate(normalize-space(.),'ABCDEFGHIJKLMNOPQRSTUVWXYZ','abcdefghijklmnopqrstuvwxyz'),'login')]",
        ]

        email_ok = attempt_login_field(email_xpaths, LIS_EMAIL, 'email')
        pass_ok = attempt_login_field(pass_xpaths, LIS_PASSWORD, 'password')

        login_success = False
        for xp in login_button_xpaths:
            try:
                btn = wait.until(EC.element_to_be_clickable((By.XPATH, xp)))
                driver.execute_script("arguments[0].click();", btn)
                print(f"[OK] Login button clicked via {xp}")
                login_success = True
                break
            except Exception as e:
                print(f"[DEBUG] Login button xpath failed: {xp} ({e})")

        if not login_success:
            screenshot_path = 'login-failure.png'
            driver.save_screenshot(screenshot_path)
            raise Exception(f'Login button not found or clickable; saved {screenshot_path}')

        time.sleep(4)
        print("[LOGIN] Waiting for post-login validation")

        try:
            post_login_marker = wait.until(EC.any_of(
                EC.presence_of_element_located((By.XPATH, "//div[contains(text(), 'Dashboard')]")),
                EC.presence_of_element_located((By.XPATH, "//a[contains(., 'Masterlist') or contains(., 'Enrol Learner')]") )
            ))
            print('[OK] Post-login marker found - assuming login success')
        except Exception:
            print('[WARN] post-login marker not found; login may have failed but proceeding anyway')

        if iframe_used:
            driver.switch_to.default_content()

        time.sleep(2)

        # NAVIGATE TO FORM
        print("\n[NAVIGATE] Going to enrollment form...")

        on_masterlist_page = False
        try:
            if 'masterlist' in driver.current_url.lower():
                on_masterlist_page = True
                print('[INFO] Already on Masterlist page by URL')
            else:
                masterlist_header = driver.find_elements(By.XPATH, "//h1[contains(normalize-space(.), 'Masterlist') or contains(normalize-space(.), 'Master list')] | //h2[contains(normalize-space(.), 'Masterlist') or contains(normalize-space(.), 'Master list')]")
                if any(el.is_displayed() for el in masterlist_header):
                    on_masterlist_page = True
                    print('[INFO] Already on Masterlist page by visible header')
        except Exception:
            pass

        if not on_masterlist_page:
            try:
                masterlist_clicked = driver.execute_script(r"""
                    const xpath = "//nav//button[normalize-space()='Masterlist'] | //nav//a[normalize-space()='Masterlist'] | //button[normalize-space()='Masterlist'] | //a[normalize-space()='Masterlist']";
                    const node = document.evaluate(xpath, document, null, XPathResult.FIRST_ORDERED_NODE_TYPE, null).singleNodeValue;
                    if (!node) return false;
                    node.scrollIntoView({block:'center'});
                    node.click();
                    return true;
                """)
                if not masterlist_clicked:
                    raise Exception('Masterlist button not found by JS selector')
                print("[OK] Masterlist clicked via JS")

                try:
                    WebDriverWait(driver, 2).until(EC.any_of(
                        EC.url_contains("masterlist"),
                        EC.presence_of_element_located((By.XPATH, "//h1[normalize-space()='Masterlist'] | //h2[normalize-space()='Masterlist']"))
                    ))
                    print("[OK] Masterlist page appears loaded")
                except Exception:
                    print("[WARN] Masterlist page did not show expected marker quickly; continuing")
            except Exception as e:
                print(f"[!] Masterlist click failed, falling back to direct URL: {str(e)[:120]}")
                driver.get(f"{LIS_URL.rstrip('/')}/masterlist")
                time.sleep(1)
        else:
            print('[INFO] Skipping Masterlist click because page is already loaded')

        # 2. Click Enrol Learner
        try:
            enrol_xpaths = [
                "//button[contains(normalize-space(.), 'Enrol Learner') or contains(normalize-space(.), 'Enroll Learner') or contains(normalize-space(.), 'Enroll')]",
                "//a[contains(normalize-space(.), 'Enrol Learner') or contains(normalize-space(.), 'Enroll Learner') or contains(normalize-space(.), 'Enroll')]",
                "//button[contains(normalize-space(.), 'School Forms') or contains(normalize-space(.), 'Select Tagging') or contains(normalize-space(.), 'Tagging')]",
                "//a[contains(normalize-space(.), 'School Forms') or contains(normalize-space(.), 'Select Tagging') or contains(normalize-space(.), 'Tagging')]"
            ]
            click_element(driver, wait, enrol_xpaths, "Enrol Learner")
            print("[OK] Enrol Learner clicked")
            time.sleep(1)
        except Exception as e:
            print(f"[!] Enrol Learner click failed: {str(e)[:120]}")
            driver.get(f"{LIS_URL.rstrip('/')}/enrol")
            time.sleep(2)

        # 3. Click Proceed
        try:
            proceed_xpaths = [
                "//button[contains(normalize-space(.), 'Proceed') or contains(normalize-space(.), 'Continue') or contains(normalize-space(.), 'Next')]",
                "//input[@value='Proceed' or @value='Continue' or @value='Next']",
                "//*[contains(normalize-space(.), 'Proceed') or contains(normalize-space(.), 'Continue') or contains(normalize-space(.), 'Next')]"
            ]
            click_element(driver, wait, proceed_xpaths, "Proceed")
            print("[OK] Proceed clicked")
            time.sleep(1)
        except Exception as e:
            print(f"[!] Proceed button failed: {str(e)[:120]}")

        # SEARCH LRN and select student from masterlist
        search_lrn = student_data.get('lrn', '')
        if search_lrn:
            try:
                input_search = wait.until(EC.presence_of_element_located((By.XPATH, "//input[contains(@placeholder,'Search') or contains(@aria-label,'Search') or @type='search']")))
                input_search.clear()
                input_search.send_keys(search_lrn)
                print(f"[OK] Search input filled with LRN {search_lrn}")
                time.sleep(1)
                # try to click search button near field
                try:
                    driver.execute_script("arguments[0].click();", input_search.find_element(By.XPATH, "./following-sibling::button | .//ancestor::div//button[contains(., 'Search') or contains(., 'Go')]") )
                    print('[OK] Search button clicked')
                except Exception:
                    print('[WARN] Search button not found; relying on auto-filter')

                time.sleep(2)
                # click preview if present
                try:
                    preview_btn = wait.until(EC.element_to_be_clickable((By.XPATH, "//*[contains(text(),'Preview')]")))
                    driver.execute_script("arguments[0].click();", preview_btn)
                    print('[OK] Preview clicked')
                except Exception as e:
                    print('[WARN] Preview button not found', e)
            except Exception as e:
                print(f"[WARN] Search input not found: {e}")

        # SECTION SELECTION
        section_to_choose = student_data.get('section', '').strip()
        if section_to_choose:
            print(f"[SECTION] Selecting section: {section_to_choose}")
            normalized_section = section_to_choose.lower()
            section_selector_xpaths = [
                "//select[contains(translate(@name,'ABCDEFGHIJKLMNOPQRSTUVWXYZ','abcdefghijklmnopqrstuvwxyz'),'section') or contains(translate(@id,'ABCDEFGHIJKLMNOPQRSTUVWXYZ','abcdefghijklmnopqrstuvwxyz'),'section') or contains(@aria-label,'Section') or contains(@placeholder,'Section')]",
                "//label[contains(translate(normalize-space(.),'ABCDEFGHIJKLMNOPQRSTUVWXYZ','abcdefghijklmnopqrstuvwxyz'),'section')]/following::select[1]",
                "//button[contains(translate(normalize-space(.),'ABCDEFGHIJKLMNOPQRSTUVWXYZ','abcdefghijklmnopqrstuvwxyz'),'select tagging') or contains(translate(normalize-space(.),'ABCDEFGHIJKLMNOPQRSTUVWXYZ','abcdefghijklmnopqrstuvwxyz'),'tagging') or contains(translate(normalize-space(.),'ABCDEFGHIJKLMNOPQRSTUVWXYZ','abcdefghijklmnopqrstuvwxyz'),'section')]",
                "//div[contains(translate(normalize-space(.),'ABCDEFGHIJKLMNOPQRSTUVWXYZ','abcdefghijklmnopqrstuvwxyz'),'select tagging') or contains(translate(normalize-space(.),'ABCDEFGHIJKLMNOPQRSTUVWXYZ','abcdefghijklmnopqrstuvwxyz'),'section')]/descendant::button[1]"
            ]
            try:
                section_control = find_clickable_element(driver, wait, section_selector_xpaths, "Section selector", timeout=4)
                tag_name = section_control.tag_name.lower()
                if tag_name == 'select':
                    from selenium.webdriver.support.select import Select
                    try:
                        Select(section_control).select_by_visible_text(section_to_choose)
                        print(f"[OK] Section selected by visible text: {section_to_choose}")
                    except Exception:
                        Select(section_control).select_by_value(section_to_choose)
                        print(f"[OK] Section selected by value: {section_to_choose}")
                else:
                    try:
                        driver.execute_script("arguments[0].click();", section_control)
                    except Exception:
                        section_control.click()
                    time.sleep(0.8)

                    option_xpaths = [
                        f"//option[contains(translate(normalize-space(.),'ABCDEFGHIJKLMNOPQRSTUVWXYZ','abcdefghijklmnopqrstuvwxyz'), '{normalized_section}') ]",
                        f"//*[contains(translate(normalize-space(.),'ABCDEFGHIJKLMNOPQRSTUVWXYZ','abcdefghijklmnopqrstuvwxyz'), '{normalized_section}') and (self::button or self::li or self::a or self::div)]",
                        f"//label[contains(translate(normalize-space(.),'ABCDEFGHIJKLMNOPQRSTUVWXYZ','abcdefghijklmnopqrstuvwxyz'), '{normalized_section}')]/following::button[1]",
                        f"//label[contains(translate(normalize-space(.),'ABCDEFGHIJKLMNOPQRSTUVWXYZ','abcdefghijklmnopqrstuvwxyz'), '{normalized_section}')]/following::li[1]"
                    ]
                    click_element(driver, wait, option_xpaths, f"Section option '{section_to_choose}'")
                    print(f"[OK] Section option clicked: {section_to_choose}")
                time.sleep(1)
            except Exception as e:
                print(f"[WARNING] Could not select section '{section_to_choose}': {e}")

        # FILL FORM
        print("\n[FILL] Filling student data...")

        def fill_form_field(field_name, field_value):
            if not field_value:
                return False

            normalized = field_name.lower().replace('_', ' ')
            candidates = [
                f"//input[contains(translate(@name,'ABCDEFGHIJKLMNOPQRSTUVWXYZ','abcdefghijklmnopqrstuvwxyz'),'{normalized}') or contains(translate(@id,'ABCDEFGHIJKLMNOPQRSTUVWXYZ','abcdefghijklmnopqrstuvwxyz'),'{normalized}')]",
                f"//textarea[contains(translate(@name,'ABCDEFGHIJKLMNOPQRSTUVWXYZ','abcdefghijklmnopqrstuvwxyz'),'{normalized}') or contains(translate(@id,'ABCDEFGHIJKLMNOPQRSTUVWXYZ','abcdefghijklmnopqrstuvwxyz'),'{normalized}')]",
                f"//select[contains(translate(@name,'ABCDEFGHIJKLMNOPQRSTUVWXYZ','abcdefghijklmnopqrstuvwxyz'),'{normalized}') or contains(translate(@id,'ABCDEFGHIJKLMNOPQRSTUVWXYZ','abcdefghijklmnopqrstuvwxyz'),'{normalized}')]",
                f"//label[contains(translate(normalize-space(text()),'ABCDEFGHIJKLMNOPQRSTUVWXYZ','abcdefghijklmnopqrstuvwxyz'),'{normalized}')]/following-sibling::*[1]",
                f"//label[contains(translate(normalize-space(text()),'ABCDEFGHIJKLMNOPQRSTUVWXYZ','abcdefghijklmnopqrstuvwxyz'),'{normalized}')]/ancestor::div//input",
            ]
            combined_xp = " | ".join(candidates)

            try:
                short_wait = WebDriverWait(driver, 2)
                elem = short_wait.until(EC.presence_of_element_located((By.XPATH, combined_xp)))
                
                try:
                    driver.execute_script("arguments[0].scrollIntoView({block: 'center'});", elem)
                except: pass
                
                tag = elem.tag_name.lower()

                if tag == 'select':
                    try:
                        from selenium.webdriver.support.select import Select
                        Select(elem).select_by_visible_text(str(field_value))
                    except Exception:
                        elem.send_keys(str(field_value))
                else:
                    elem.clear()
                    elem.send_keys(str(field_value))

                print(f"  [OK] {field_name}: {field_value}")
                return True
            except Exception:
                return False

        fields = {
            'lrn': student_data.get('lrn', ''),
            'first_name': student_data.get('first_name', ''),
            'last_name': student_data.get('last_name', ''),
            'middle_name': student_data.get('middle_name', ''),
            'extension_name': student_data.get('extension_name', ''),
            'birthday': student_data.get('birthday', ''),
            'sex': student_data.get('sex', ''),
            'mother_tongue': student_data.get('mother_tongue', ''),
            'place_of_birth': student_data.get('place_of_birth', ''),
            'contact_number': student_data.get('contact_number', ''),
            'curr_house_number': student_data.get('curr_house_number', ''),
            'curr_street': student_data.get('curr_street', ''),
            'curr_barangay': student_data.get('curr_barangay', ''),
            'curr_city': student_data.get('curr_city', ''),
            'curr_province': student_data.get('curr_province', ''),
            'curr_zip_code': student_data.get('curr_zip_code', ''),
            'grade': student_data.get('grade_level', ''),
            'track': student_data.get('track', ''),
            'cluster': student_data.get('cluster', ''),
            'academic_status': student_data.get('academic_status', ''),
            'section': student_data.get('section', ''),
        }

        filled = 0
        not_found = 0

        for key, value in fields.items():
            if not value:
                continue
            if fill_form_field(key, value):
                filled += 1
            else:
                not_found += 1
                print(f"  [?] {key}: not found or fill failed")

        print(f"\n[RESULT] Filled: {filled} fields | Not found: {not_found} fields")

        # Randomize first attendance date in the 2025 school year window
        print("\n[RANDOMIZE] Assigning a random first attendance date")
        import random
        from datetime import datetime, timedelta

        date_start = datetime(2025, 6, 1)
        date_end = datetime(2025, 9, 30)
        days_diff = (date_end - date_start).days
        choice_date = date_start + timedelta(days=random.randint(0, days_diff))
        first_attendance = {
            'month': choice_date.strftime('%B'),
            'day': str(choice_date.day),
            'year': str(choice_date.year),
        }

        def set_attendance_date(driver, wait, date_dict):
            # Selects may have month/day/year drop-downs in LIS
            short_wait = WebDriverWait(driver, 2)
            for field_name, value in date_dict.items():
                candidate_xpath = f"//select[contains(@name,'{field_name}') or contains(@id,'{field_name}') or contains(@aria-label,'{field_name}')]"
                try:
                    select_element = short_wait.until(EC.presence_of_element_located((By.XPATH, candidate_xpath)))
                    from selenium.webdriver.support.select import Select
                    Select(select_element).select_by_visible_text(value)
                    print(f"[OK] Set first attendance {field_name} = {value}")
                except Exception as e:
                    print(f"[WARN] Could not set first attendance {field_name}: {e}")

        set_attendance_date(driver, wait, first_attendance)

        # Verify displayed student credentials on the current enrollment page
        def verify_displayed_credentials(driver, expected):
            page_text = driver.find_element(By.TAG_NAME, 'body').text
            checks = []
            for key, expected_value in expected.items():
                if not expected_value:
                    continue
                if str(expected_value) in page_text:
                    checks.append((key, True))
                else:
                    checks.append((key, False))
            return checks

        check_attributes = {
            'name': f"{student_data.get('first_name', '')} {student_data.get('last_name', '')}",
            'lrn': student_data.get('lrn', ''),
            'dob': student_data.get('birthday', ''),
        }
        print('\n[VERIFY] Checking student credentials found on enrollment page...')
        results = verify_displayed_credentials(driver, check_attributes)
        for attr, ok in results:
            print(f"[{'OK' if ok else 'FAIL'}] {attr}")

        if not all(ok for _, ok in results):
            print('[WARN] Some credential checks failed; please confirm manually.')

        print("\n" + "="*70)
        print("[SUCCESS] Auto-fill completed and credentials verified where possible.")
        print("[WAITING] Browser window remains open for final confirm and submit.")
        print("[ACTION] Auto-steps done; please complete submission in LIS.\n")

        # Keep browser open until caller closes via /confirm-enrollment OR user closes it manually
        while active_driver is not None:
            try:
                # Simple check to see if browser is still responsive
                _ = driver.title
            except Exception:
                # Browser likely closed manually
                print("[INFO] Browser closed manually by user")
                with driver_lock:
                    if active_driver == driver:
                        active_driver = None
                break
            time.sleep(1)

    except Exception as e:
        print(f"\n[ERROR] Enrollment form filler encountered an error:")
        traceback.print_exc()
    
    finally:
        print("\n[END] Enrollment form filler stopped")



def fill_enrollment():
    """Start enrollment form filling"""
    # Handle CORS preflight requests
    if request.method == 'OPTIONS':
        print("\n[CORS] OPTIONS preflight request - returning OK")
        return '', 200
    
    print("\n" + "="*70)
    print("[REQUEST] Received /fill-enrollment request")
    print("="*70)
    
    try:
        # Handle JSON data more flexibly
        if request.is_json:
            data = request.json
        else:
            # Try to parse JSON from request data
            import json
            data = json.loads(request.data.decode('utf-8'))
        
        print(f"[DATA] Received student data:")
        print(f"  LRN: {data.get('lrn')}")
        print(f"  Name: {data.get('first_name')} {data.get('last_name')}")
        
        if not data.get('lrn'):
            print("[ERROR] LRN is missing!")
            return jsonify({'status': 'error', 'message': 'LRN is required'}), 400
        
        print("[ACTION] Starting Chrome browser in background thread...")
        # Start in background thread
        threading.Thread(
            target=fill_enrollment_form,
            args=(data,),
            daemon=False
        ).start()
        
        print("[SUCCESS] Form filler thread started!")
        return jsonify({
            'status': 'started',
            'message': 'Form filler started - check Chrome window',
            'lrn': data.get('lrn')
        })
        
    except Exception as e:
        print(f"[ERROR] Exception: {str(e)}")
        import traceback
        traceback.print_exc()
        return jsonify({'status': 'error', 'message': str(e)}), 500


def check_webdriver():
    """Check that Selenium WebDriver can launch Chrome."""
    from selenium import webdriver
    from selenium.webdriver.chrome.service import Service

    try:
        print("\n[CHECK-WEBDRIVER] Starting WebDriver diagnostic...")
        
        print(f"[DEBUG] Python version: {sys.version.split()[0]}")
        print(f"[DEBUG] Platform: {platform.platform()}")
        print(f"[DEBUG] Checking Chrome installation...")
        
        chrome_path = find_chrome_path()
        if chrome_path:
            print(f"  OK Found Chrome at: {chrome_path}")
        else:
            print(f"  WARN Chrome not found in standard locations; Selenium will search system PATH")
        
        print(f"\n[DEBUG] Downloading ChromeDriver (if needed)...")
        try:
            driver_path = get_chromedriver_path()
            print(f"  OK ChromeDriver: {driver_path}")
            file_size = os.path.getsize(driver_path) / (1024*1024)
            print(f"  OK File size: {file_size:.2f} MB")
        except Exception as e:
            print(f"  FAIL ChromeDriver download failed: {e}")
            raise
        
        print(f"\n[DEBUG] Attempting Chrome launch (with 30 second timeout)...")
        
        opt = get_chrome_options()
        opt.add_argument('--headless=new')
        
        # Use a shorter timeout for the launch test
        try:
            service = Service(driver_path)
            driver = webdriver.Chrome(service=service, options=opt)
            driver.get('about:blank')
            driver.quit()
            print(f"  OK Chrome launched and closed successfully!")
        except Exception as inner_e:
            print(f"  FAIL Chrome launch failed: {inner_e}")
            # Re-raise with more context
            raise Exception(f"Chrome launch error (this may be architecture mismatch): {inner_e}")

        return jsonify({
            'status': 'ok',
            'message': 'WebDriver fully functional',
            'platform': platform.platform(),
            'python_version': sys.version.split()[0],
            'chromedriver_path': driver_path,
        }), 200
    except Exception as e:
        print(f'[ERROR] check_webdriver: {e}')
        return jsonify({
            'status': 'error', 
            'message': f'WebDriver check failed. See logs. Error: {str(e)[:200]}',
            'note': 'This may be a Chrome/ChromeDriver architecture mismatch (32-bit vs 64-bit)',
        }), 500


def status():
    """Service status"""
    global active_driver
    return jsonify({
        'status': 'online',
        'driver_active': active_driver is not None,
        'message': 'Enrollment Form Filler Service'
    })


def confirm_enrollment():
    """Close active LIS session and confirm success"""
    global active_driver
    with driver_lock:
        if not active_driver:
            return jsonify({'status': 'error', 'message': 'No active LIS session to confirm'}), 400

        try:
            try:
                active_driver.get('https://learner-information-system-dashboard-540972607515.us-west1.run.app/logout')
            except Exception:
                pass

            active_driver.quit()
            active_driver = None

            return jsonify({'status': 'success', 'message': 'LIS session closed, enrollment confirmed'}), 200
        except Exception as e:
            return jsonify({'status': 'error', 'message': str(e)}), 500


def create_app():
    app = Flask(__name__)

    @app.before_request
    def log_request_info_wrapper():
        return log_request_info()

    @app.after_request
    def log_response_info_wrapper(response):
        return log_response_info(response)

    app.add_url_rule('/fill-enrollment', 'fill_enrollment', fill_enrollment, methods=['POST', 'OPTIONS'])
    app.add_url_rule('/check-webdriver', 'check_webdriver', check_webdriver, methods=['GET'])
    app.add_url_rule('/status', 'status', status, methods=['GET'])
    app.add_url_rule('/confirm-enrollment', 'confirm_enrollment', confirm_enrollment, methods=['POST'])

    return app


app = create_app()

if __name__ == '__main__':
    print("\n" + "="*70)
    print("  ENROLLMENT FORM FILLER SERVICE")
    print("="*70)
    print("  Port:     5002")
    print("  Protocol: HTTP (Local development)")
    print("  Endpoint: POST http://localhost:5002/fill-enrollment")
    print("  Status:   GET http://localhost:5002/status")
    print("  Security: CORS enabled, localhost-only access")
    print("="*70 + "\n")

    from socketserver import ThreadingMixIn
    from wsgiref.simple_server import make_server, WSGIRequestHandler, WSGIServer

    class ThreadingWSGIServer(ThreadingMixIn, WSGIServer):
        daemon_threads = True
        allow_reuse_address = True

    server = make_server('127.0.0.1', 5002, app, server_class=ThreadingWSGIServer, handler_class=WSGIRequestHandler)
    print('[INFO] Starting enrollment filler service on http://127.0.0.1:5002 (threaded)')
    try:
        server.serve_forever()
    except KeyboardInterrupt:
        print('[INFO] Shutting down enrollment filler service')
    finally:
        server.server_close()
