#!/usr/bin/env python3
import os
import shutil
from pathlib import Path

migrations_dir = Path("database/migrations")
archive_dir = Path("database/migrations_archive")

# Files to keep
keep_patterns = [
    "0001_",  # Laravel system migrations
    "2025_10_02_200000",  # New comprehensive migration
    "2025_10_02_100332",  # Old comprehensive migration (backup)
]

moved = 0
kept = 0

for migration_file in migrations_dir.glob("*.php"):
    filename = migration_file.name

    # Check if we should keep this file
    should_keep = any(pattern in filename for pattern in keep_patterns)

    if should_keep:
        print(f"✅ Keeping: {filename}")
        kept += 1
    else:
        # Move to archive
        dest = archive_dir / filename
        shutil.move(str(migration_file), str(dest))
        print(f"📦 Archived: {filename}")
        moved += 1

print(f"\n✅ Done!")
print(f"Kept: {kept} migrations")
print(f"Archived: {moved} migrations")
