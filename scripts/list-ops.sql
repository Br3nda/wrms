SELECT am.amname AS acc_name, opc.opcname AS ops_name, opr.oprname AS ops_comp
	FROM pg_am am, pg_amop amop, pg_opclass opc, pg_operator opr
	WHERE amop.amopid = am.oid AND amop.amopclaid = opc.oid AND amop.amopopr = opr.oid
   ORDER BY acc_name, ops_name, ops_comp;
