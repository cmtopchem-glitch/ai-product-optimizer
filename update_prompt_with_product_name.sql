-- Update User Prompt to include product_name translation
-- This adds product_name to the JSON response format

UPDATE gm_configuration
SET gm_value = REPLACE(
    gm_value,
    'ANTWORT-FORMAT (NUR JSON, KEINE MARKDOWN-BLÖCKE):
{
  "product_description":',
    'ANTWORT-FORMAT (NUR JSON, KEINE MARKDOWN-BLÖCKE):
{
  "product_name": "Übersetzter Produktname in Zielsprache",
  "product_description":'
)
WHERE gm_key = 'OPENAI_USER_PROMPT'
  AND gm_value LIKE '%ANTWORT-FORMAT%'
  AND gm_value NOT LIKE '%"product_name":%';

-- Alternative: Reset to default prompt (uncomment if needed)
-- UPDATE gm_configuration
-- SET gm_value = ''
-- WHERE gm_key = 'OPENAI_USER_PROMPT';
