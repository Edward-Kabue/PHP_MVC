# Copyright (c) 2023, Edward and contributors
# For license information, please see license.txt

# import frappe
from __future__ import unicode_literals
import frappe
from frappe.model.document import Document


class Company(Document):
	pass
def on_update(doc, method):
    if doc.get('__islocal'):
        # New company added, create default credit limit
        credit_limit = doc.net_salary * 0.5
        frappe.get_doc({
            'doctype': 'Credit Limit',
            'company': doc.company_name,
            'credit_limit': credit_limit
        }).insert()

        # Create new user with access to 'ams' workspace
        user = frappe.get_doc({
            'doctype': 'User',
            'email': doc.email_id,
            'first_name': doc.company_name,
            'send_welcome_email': 1,
            'roles': [{
                'role': 'System Manager'
            }],
            'user_type': 'Website User',
            'default_company': doc.name,
            'default_currency': 'USD',
            'language': 'en',
            'enabled': 1,
            'user_permissions': [{
                'allow': 1,
                'doctype': 'Workspace',
                'name': 'ams'
            }]
        })
        user.flags.ignore_permissions = True
        user.insert()