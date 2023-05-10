from setuptools import setup, find_packages

with open("requirements.txt") as f:
	install_requires = f.read().strip().split("\n")

# get version from __version__ variable in ams/__init__.py
from ams import __version__ as version

setup(
	name="ams",
	version=version,
	description="salary advancce",
	author="Edward",
	author_email="ekabue0@gmail.com",
	packages=find_packages(),
	zip_safe=False,
	include_package_data=True,
	install_requires=install_requires
)
