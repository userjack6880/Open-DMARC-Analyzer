CREATE VIEW report_stats AS (
 SELECT
  report.serial, domain, rcount, disposition, reason, 
  policy_p, policy_pct, dkimdomain, dkimresult, dkim_align, 
  spfdomain, spfresult, spf_align, mindate, maxdate
 FROM report RIGHT JOIN rptrecord
  ON report.serial=rptrecord.serial
);
