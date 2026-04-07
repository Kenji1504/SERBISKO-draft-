#!/usr/bin/env python
"""Test the enrollment form filler service"""
import requests
import json
import time
import sys

SERVICE_URL = "http://localhost:5002"

def test_status():
    """Test /status endpoint"""
    print("\n[TEST] Checking service status...")
    try:
        resp = requests.get(f"{SERVICE_URL}/status", timeout=5)
        data = resp.json()
        print(f"  ✓ Status: {data.get('status')}")
        print(f"  ✓ Response: {data}")
        return True
    except Exception as e:
        print(f"  ✗ Failed: {e}")
        return False

def test_webdriver():
    """Test /check-webdriver endpoint"""
    print("\n[TEST] Checking WebDriver setup...")
    print("  (This may take 10-20 seconds as it downloads ChromeDriver and launches Chrome...)")
    try:
        resp = requests.get(f"{SERVICE_URL}/check-webdriver", timeout=60)
        data = resp.json()
        print(f"  ✓ Status: {data.get('status')}")
        print(f"  ✓ Message: {data.get('message')}")
        if data.get('python_version'):
            print(f"  ✓ Python: {data.get('python_version')[:50]}...")
        if data.get('platform'):
            print(f"  ✓ Platform: {data.get('platform')}")
        return data.get('status') == 'ok'
    except requests.exceptions.Timeout:
        print(f"  ✗ Timeout (60s) - check terminal logs for errors")
        return False
    except Exception as e:
        print(f"  ✗ Failed: {e}")
        return False

def test_enrollment():
    """Test /fill-enrollment endpoint"""
    print("\n[TEST] Testing enrollment form filler...")
    payload = {
        "lrn": "00634655172",
        "first_name": "TYRIQUE",
        "last_name": "BRAKUS",
        "middle_name": "K",
        "birthday": "2005-06-15",
        "sex": "Male",
        "section": "Grade 11 - Bezos (SY 2025-2026)"
    }
    
    print(f"  Sending data: {json.dumps(payload, indent=4)}")
    
    try:
        resp = requests.post(f"{SERVICE_URL}/fill-enrollment", json=payload, timeout=10)
        print(f"  ✓ Response status: {resp.status_code}")
        data = resp.json()
        print(f"  ✓ Response: {json.dumps(data, indent=4)}")
        
        if resp.status_code == 200 and data.get('status') == 'started':
            print("\n  ⚠ A Chrome window should be opening now...")
            print("  ⚠ Check if it logs into LIS and fills the form.")
            return True
        else:
            print(f"\n  ✗ Unexpected response")
            return False
    except Exception as e:
        print(f"  ✗ Failed: {e}")
        import traceback
        traceback.print_exc()
        return False

if __name__ == "__main__":
    print("="*70)
    print("  ENROLLMENT FORM FILLER SERVICE - TEST SUITE")
    print("="*70)
    
    # Test 1: Status
    if not test_status():
        print("\n✗ Service is not running!")
        print("  Start it with: cd python_services && py enrollment_form_filler.py")
        sys.exit(1)
    
    # Test 2: WebDriver
    print("\n[INFO] WebDriver check will now run...")
    print("       This requires Chrome to be installed and compatible with Python architecture.")
    webdriver_ok = test_webdriver()
    
    if not webdriver_ok:
        print("\n⚠ WebDriver check failed. Possible causes:")
        print("  1. Chrome binary not found or incompatible (32-bit vs 64-bit)")
        print("  2. ChromeDriver architecture mismatch")
        print("  3. Python environment issue")
        print("\nCheck the Flask terminal logs for more details.")
        print("\nContinuing with enrollment test anyway...\n")
    
    # Test 3: Enrollment (will fail gracefully if WebDriver is broken)
    time.sleep(2)
    test_enrollment()
    
    print("\n" + "="*70)
    print("  Tests completed. Check Chrome window and Flask logs!")
    print("="*70)
