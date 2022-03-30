# Versioning-Scheme

This is a general guideline for all projects controlled by John Bradley (userjack6880) or part of the System Anomaly sphere of influence. This scheme shall be called the Anomaly Versioning Scheme (2022).

## Overview
```
Major - (Stage) SubMajor . Minor

ex

Alpha Project:     0-α1.6    Version 0 Alpha 1.6
Feature Complete:  1-fc      Version 1 Feature Complete
Beta Project:      1-β4      Version 1 Beta 4
Release Candidate: 5-rc2     Version 5 Release Candidate 2
Stable:            1         Version 1
Stable Update:     1-u3      Version 1 Update 3
```

## Stages
Project stages are defined as followed:
- Alpha: the project is still in a state where features may be added and serious errors and fixes are not the primary focus of development. This is the "get shit working" stage of development. Alpha versions are not production ready. These versions are denoted with the Greek letter α or the word "Alpha".
- Feature Complete: this project is still not in a state where fixes are the primary focus of development, but no new features are added. Existing features are fleshed out for full functionality before moving onto beta. It's still technically an Alpha version and is not production ready. Usually there will only be one feature complete version. These versions are denoted with "fc" or "Feature Complete".
- Beta: the project has moved onto bug fixes and error correcting. This is where everything is tested and polished. While not considered production ready, these versions can be put into production at the risk of the user, as no new features will be added. These version are denoted with the Greek letter β or the word "Beta".
- Release Candidate: the project is ready for release. It is assumed this version will become a stable release if no major bugs are found. Fixes are only applied to the Beta Stage and pushed back up to a new Release Candidate version. During this stage, documentation is completed. These versions are denoted with "rc" or "Release Candidate".
- Stable: the project is finally released. No major bugs are known, all features are implemented, and the software is production safe. These have no denotations unless an update is issued.
- Stable Update: A major fix was applied or a minor feature update was issued. These are denoted with "u" or "Update".

## Major Versions
Major versions are established in one of two ways. Initial project creation has a major version number of 0, but are incremented to version 1 once it becomes feature complete. Feature completeness means that no major features will be added to this version. Additional features are agregated into the next version, which may begin as soon as a version exits alpha and begins beta testing.

## Submajor Versions
Submajor version are limited to the stage of development in which the code was changed. Submajor changes also vary depending on stage of development.

- Alpha submajor versions include significant rewrites of the code or new features added.
- Feature Complete code does not have a submajor.
- Beta submajor versions are included when a major bug is fixed, a feature is fleshed out, or there is a significant rewrite to accomplished either goal.
- Release Candidate submajor versions are issued if a major bug is found and fixed. The first submajor release candidate version will not have a number attached.
- Stable submajor versions are issued if a major bug is found and fixed or a feature is tweaked after release. Stable submajor versions add the stage indicater "u" or "Update".

## Minor Versions
Minor versions are updates to submajor versions. Like submajor versions, these also vary depending on stage of development.

- Alpha minor versions are very minor code changes, like a small tweak to a variable or changing single digit lines.
- Feature Complete code does not have minor versions.
- Beta minor versions are like Alpha minor versions, and are very minor code changes.
- Release Candidate code does not have minor versions.
- Stable code does not have minor versions.

# Implementation
This versioning system will be implemented on date of publish (29 March, 2022), and projects will be updated accordingly.