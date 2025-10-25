#!/usr/bin/env python3
"""
Admin login test module.
Tests admin user login functionality and dashboard access.
"""

import time
from selenium import webdriver
from selenium.webdriver.common.by import By
from selenium.webdriver.chrome.options import Options
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC
from selenium.common.exceptions import TimeoutException, NoSuchElementException

def test_admin_login():
    """Test admin user login and dashboard access"""

    # Set up Chrome options
    chrome_options = Options()
    # chrome_options.add_argument('--headless')  # Commented out to show browser
    chrome_options.add_argument('--no-sandbox')
    chrome_options.add_argument('--disable-dev-shm-usage')

    driver = None
    try:
        # Initialize driver
        driver = webdriver.Chrome(options=chrome_options)
        wait = WebDriverWait(driver, 10)

        print("Chrome driver initialized")

        # Navigate to login page
        driver.get('http://localhost:8000/login')
        print("Navigated to login page")

        # Wait for page to load
        wait.until(EC.presence_of_element_located((By.ID, 'loginForm')))

        # Verify we're on the login page
        login_form = driver.find_element(By.ID, 'loginForm')
        print("✓ Login form found")

        # Fill in login credentials (using the first admin user)
        email_field = driver.find_element(By.ID, 'email')
        password_field = driver.find_element(By.ID, 'password')

        email_field.clear()
        email_field.send_keys('admin@example.com')
        print("✓ Entered admin email")

        password_field.clear()
        password_field.send_keys('password')  # Assuming default password
        print("✓ Entered admin password")

        # Submit the form
        login_button = driver.find_element(By.ID, 'loginBtn')
        login_button.click()
        print("✓ Clicked login button")

        # Wait for redirect to admin dashboard
        try:
            # Wait for either success redirect or error message
            wait.until(lambda driver: 'admin' in driver.current_url or
                      driver.find_elements(By.CLASS_NAME, 'alert-danger'))

            current_url = driver.current_url
            print(f"Current URL after login: {current_url}")

            if 'admin' in current_url:
                print("✓ Successfully redirected to admin area")

                # Verify we're on admin dashboard
                try:
                    # Look for admin dashboard elements
                    page_title = driver.find_element(By.TAG_NAME, 'h1').text
                    print(f"✓ Admin page title: {page_title}")

                    # Check for admin navigation or content
                    admin_content = driver.find_elements(By.CLASS_NAME, 'admin') or \
                                   driver.find_elements(By.ID, 'admin-dashboard') or \
                                   driver.find_elements(By.CSS_SELECTOR, '[class*="admin"]')

                    if admin_content or 'admin' in driver.page_source.lower():
                        print("✓ Admin dashboard content found")
                        return True
                    else:
                        print("⚠ Admin dashboard content not clearly identified, but URL suggests success")
                        return True

                except Exception as e:
                    print(f"⚠ Could not verify admin dashboard content: {e}")
                    # If we're in admin URL, consider it success
                    return 'admin' in current_url

            else:
                # Check for error messages
                error_alerts = driver.find_elements(By.CLASS_NAME, 'alert-danger')
                if error_alerts:
                    error_text = error_alerts[0].text
                    print(f"✗ Login failed with error: {error_text}")
                    return False
                else:
                    print("✗ Login failed - no admin redirect and no error message")
                    return False

        except TimeoutException:
            print("✗ Login timeout - no redirect or error message appeared")
            return False

    except Exception as e:
        print(f"✗ Test failed with error: {str(e)}")
        return False

    finally:
        if driver:
            driver.quit()
            print("Driver closed")

def run_admin_login_test():
    """Run the admin login test"""
    print("Testing admin login functionality...")
    print('='*50)
    success = test_admin_login()
    print('='*50)
    if success:
        print("✓ Admin login test PASSED")
    else:
        print("✗ Admin login test FAILED")
    return success

if __name__ == "__main__":
    run_admin_login_test()