SELECT perorg_request.request_id INTO tempt FROM perorg_request, perorg_request AS po2 WHERE perorg_request.request_id=po2.request_id AND perorg_request.perorg_id=2 AND po2.perorg_id=11;
DELETE FROM perorg_request WHERE perorg_id=2 AND request_id=tempt.request_id;
UPDATE perorg_request SET perorg_id=11 WHERE perorg_id=2;
