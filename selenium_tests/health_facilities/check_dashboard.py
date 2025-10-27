#!/usr/bin/env python3
"""
Dashboard check test module.
This module verifies that the health facility dashboard loads correctly.
"""

from selenium.webdriver.common.by import By
from shared.base_test import BaseTest

class CheckDashboardTest(BaseTest):
    """Test class for checking dashboard loading"""

    def run_test(self):
        """Run the dashboard check test"""
        print("Starting dashboard check test...")

        try:
            # Navigate to health facility dashboard
            self.navigate_to_health_facility_dashboard()

            # Check if redirected to KETI AI (indicating server not running or facility not found)
            if "ketiai.com" in self.driver.current_url or self.driver.title == "KETI AI":
                print("✗ Redirected to KETI AI site - health facility may not exist or server not running")
                print("Current URL:", self.driver.current_url)
                return False

            # Check dashboard elements
            if self.check_dashboard_loaded():
                print("✓ Dashboard check test passed")
                return True
            else:
                print("✗ Dashboard check test failed")
                return False

        except Exception as e:
            print(f"✗ Error during dashboard check: {str(e)}")
            return False

def run_dashboard_check():
    """Standalone function to run the dashboard check"""
    test = CheckDashboardTest()
    try:
        test.setup_driver()
        return test.run_test()
    finally:
        test.teardown_driver()

if __name__ == "__main__":
    run_dashboard_check()