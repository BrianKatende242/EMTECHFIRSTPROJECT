from selenium import webdriver
from selenium.webdriver.common.by import By
from selenium.webdriver.support.ui import Select
from datetime import datetime, timedelta
import time

class BaseTest:
    """Base class for selenium tests with common utilities"""

    def __init__(self):
        self.driver = None

    def setup_driver(self):
        """Initialize the Chrome webdriver"""
        self.driver = webdriver.Chrome()
        print("Chrome driver initialized")

    def teardown_driver(self):
        """Close the webdriver"""
        if self.driver:
            self.driver.quit()
            print("Driver closed")

    def wait(self, seconds=2):
        """Wait for specified seconds"""
        time.sleep(seconds)

    def navigate_to_health_facility_dashboard(self, facility_id=1):
        """Navigate to health facility dashboard"""
        url = f"http://localhost:8000/health-facility/dashboard/{facility_id}"
        self.driver.get(url)
        self.wait(2)
        print(f"Navigated to health facility dashboard: {url}")

    def check_dashboard_loaded(self):
        """Check if dashboard loaded successfully"""
        try:
            patients_card = self.driver.find_element(By.XPATH, "//div[contains(text(), 'Patients')]")
            appointments_card = self.driver.find_element(By.XPATH, "//div[contains(text(), 'Appointments')]")
            print("✓ Health facility dashboard loaded successfully")
            return True
        except Exception as e:
            print(f"✗ Health facility dashboard elements not found: {str(e)}")
            return False

    def navigate_to_appointments(self):
        """Navigate to appointments page"""
        appointments_link = self.driver.find_element(By.LINK_TEXT, "Appointments")
        appointments_link.click()
        self.wait(5)
        print("Navigated to appointments page")

    def navigate_to_patients(self):
        """Navigate to patients page"""
        patients_link = self.driver.find_element(By.LINK_TEXT, "Patients")
        patients_link.click()
        self.wait(3)
        print("Navigated to patients page")

    def navigate_to_doctor_appointments(self, doctor_id=1):
        """Navigate to doctor's appointments page"""
        url = f"http://localhost:8000/doctor/{doctor_id}/appointments"
        self.driver.get(url)
        self.wait(3)
        print(f"Navigated to doctor appointments: {url}")

    def get_future_datetime_str(self, days_ahead=365, hour=7):
        """Get future datetime string in UTC format"""
        future_time = datetime.now() + timedelta(days=days_ahead)
        future_time = future_time.replace(hour=hour, minute=0, second=0, microsecond=0)
        # Convert to UTC (EAT is UTC+3)
        utc_time = future_time - timedelta(hours=3)
        datetime_str = f"{utc_time.year}-{utc_time.month:02d}-{utc_time.day:02d}T{utc_time.hour:02d}:{utc_time.minute:02d}"
        return datetime_str

    def set_datetime_input(self, datetime_str):
        """Set the appointment datetime input"""
        script = f"document.getElementById('appointment_time').value = '{datetime_str}';"
        self.driver.execute_script(script)
        print(f"Set datetime to: {datetime_str} (UTC)")

    def check_for_alerts(self):
        """Check for success or error alerts on the page"""
        try:
            success_alert = self.driver.find_element(By.CLASS_NAME, "alert-success")
            print(f"✓ Success alert: {success_alert.text}")
            return True
        except:
            try:
                light_alert = self.driver.find_element(By.CLASS_NAME, "alert-light")
                if "success" in light_alert.text.lower() or "booked" in light_alert.text.lower():
                    print(f"✓ Success alert: {light_alert.text}")
                    return True
                else:
                    print(f"Alert found: {light_alert.text}")
                    return False
            except:
                try:
                    error_alert = self.driver.find_element(By.CLASS_NAME, "alert-danger")
                    print(f"✗ Error alert: {error_alert.text}")
                    return False
                except:
                    print("No alerts found")
                    return None

    def check_modal_errors(self, modal_id="bookDoctorModal"):
        """Check if modal is still open and has errors"""
        try:
            modal = self.driver.find_element(By.ID, modal_id)
            if modal.is_displayed():
                print("Modal still open - checking for errors")
                error_elements = self.driver.find_elements(By.CLASS_NAME, "text-danger")
                if error_elements:
                    for error in error_elements:
                        print(f"Error: {error.text}")
                    return True  # Has errors
                else:
                    print("No error messages found in modal")
                    return False
            else:
                print("Modal closed")
                return False
        except:
            print("Modal not found")
            return False