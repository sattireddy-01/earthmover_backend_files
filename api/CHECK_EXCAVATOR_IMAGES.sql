-- =====================================================
-- CHECK: Verify Excavator Images in Database
-- This query shows all excavators (category_id = 2) and their images
-- =====================================================

SELECT 
    machine_id,
    operator_id,
    machine_model,
    price_per_hour,
    image,
    machine_image_1,
    CASE 
        WHEN machine_image_1 IS NOT NULL AND machine_image_1 != '' THEN machine_image_1
        WHEN image IS NOT NULL AND image != '' THEN image
        ELSE 'NO IMAGE'
    END AS image_used,
    CASE 
        WHEN machine_image_1 IS NOT NULL AND machine_image_1 != '' THEN 'machine_image_1'
        WHEN image IS NOT NULL AND image != '' THEN 'image'
        ELSE 'NONE'
    END AS image_source
FROM machines
WHERE category_id = 2
ORDER BY machine_id;

-- Check if images are unique
SELECT 
    machine_image_1,
    COUNT(*) AS count
FROM machines
WHERE category_id = 2
AND machine_image_1 IS NOT NULL
GROUP BY machine_image_1
HAVING COUNT(*) > 1;

-- Check if all excavators have images
SELECT 
    COUNT(*) AS total_excavators,
    SUM(CASE WHEN machine_image_1 IS NOT NULL AND machine_image_1 != '' THEN 1 ELSE 0 END) AS with_machine_image_1,
    SUM(CASE WHEN image IS NOT NULL AND image != '' THEN 1 ELSE 0 END) AS with_image,
    SUM(CASE WHEN (machine_image_1 IS NULL OR machine_image_1 = '') AND (image IS NULL OR image = '') THEN 1 ELSE 0 END) AS without_images
FROM machines
WHERE category_id = 2;












