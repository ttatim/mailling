UPDATE plans SET name='Uso Único — 10 dias', amount_cents=2990, duration_days=10, is_recurring=FALSE, active=TRUE WHERE code='single';
INSERT INTO plans (code,name,amount_cents,duration_days,is_recurring,active)
SELECT 'single_30','Uso Único — 30 dias',3990,30,FALSE,TRUE
WHERE NOT EXISTS (SELECT 1 FROM plans WHERE code='single_30');
