-- Sadaka Module Schema Fixes
-- Adds missing constraints, indexes, and improvements

ALTER TABLE sadaka_entries 
  ADD CONSTRAINT chk_entry_month CHECK (entry_month >= 1 AND entry_month <= 12),
  ADD CONSTRAINT chk_entry_week CHECK (entry_week IS NULL OR (entry_week >= 1 AND entry_week <= 4)),
  ADD CONSTRAINT chk_amount CHECK (amount > 0 AND amount <= 9999999.99),
  ADD INDEX idx_entered_by (entered_by),
  ADD UNIQUE KEY uk_member_category_date_week (member_id, category_id, entry_date, COALESCE(entry_week, 0));

-- Add more descriptive comment on sadaka_categories
ALTER TABLE sadaka_categories COMMENT = 'Categories for sadaka/offerings: Love, Development, Tithes, Pledges';

-- Add comment on sadaka_entries
ALTER TABLE sadaka_entries COMMENT = 'Individual member contribution records with audit trail';

-- Add comment on sadaka_uploads
ALTER TABLE sadaka_uploads COMMENT = 'Track bulk import history with error logs for audit purposes';
