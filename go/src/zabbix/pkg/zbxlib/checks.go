/*
** Zabbix
** Copyright (C) 2001-2019 Zabbix SIA
**
** This program is free software; you can redistribute it and/or modify
** it under the terms of the GNU General Public License as published by
** the Free Software Foundation; either version 2 of the License, or
** (at your option) any later version.
**
** This program is distributed in the hope that it will be useful,
** but WITHOUT ANY WARRANTY; without even the implied warranty of
** MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
** GNU General Public License for more details.
**
** You should have received a copy of the GNU General Public License
** along with this program; if not, write to the Free Software
** Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
**/

package zbxlib

/* cspell:disable */

/*
#cgo CFLAGS: -I${SRCDIR}/../../../../../include

#include "common.h"
#include "sysinfo.h"
#include "module.h"
typedef int (*zbx_agent_check_t)(AGENT_REQUEST *request, AGENT_RESULT *result);

int	SYSTEM_LOCALTIME(AGENT_REQUEST *request, AGENT_RESULT *result);
int	NET_DNS(AGENT_REQUEST *request, AGENT_RESULT *result);
int	NET_DNS_RECORD(AGENT_REQUEST *request, AGENT_RESULT *result);
int	PROC_MEM(AGENT_REQUEST *request, AGENT_RESULT *result);
int	PROC_NUM(AGENT_REQUEST *request, AGENT_RESULT *result);
int	SYSTEM_BOOTTIME(AGENT_REQUEST *request, AGENT_RESULT *result);
int	WEB_PAGE_GET(AGENT_REQUEST *request, AGENT_RESULT *result);
int	WEB_PAGE_PERF(AGENT_REQUEST *request, AGENT_RESULT *result);
int	WEB_PAGE_REGEXP(AGENT_REQUEST *request, AGENT_RESULT *result);
int	NET_TCP_LISTEN(AGENT_REQUEST *request, AGENT_RESULT *result);
int	NET_TCP_PORT(AGENT_REQUEST *request, AGENT_RESULT *result);
int	CHECK_SERVICE(AGENT_REQUEST *request, AGENT_RESULT *result);
int	CHECK_SERVICE_PERF(AGENT_REQUEST *request, AGENT_RESULT *result);
int	NET_UDP_LISTEN(AGENT_REQUEST *request, AGENT_RESULT *result);
int	GET_SENSOR(AGENT_REQUEST *request, AGENT_RESULT *result);
int	SYSTEM_CPU_LOAD(AGENT_REQUEST *request, AGENT_RESULT *result);
int	SYSTEM_CPU_SWITCHES(AGENT_REQUEST *request, AGENT_RESULT *result);
int	SYSTEM_CPU_INTR(AGENT_REQUEST *request, AGENT_RESULT *result);
int	SYSTEM_HW_CHASSIS(AGENT_REQUEST *request, AGENT_RESULT *result);
int	SYSTEM_HW_CPU(AGENT_REQUEST *request, AGENT_RESULT *result);
int	SYSTEM_HW_DEVICES(AGENT_REQUEST *request, AGENT_RESULT *result);
int	SYSTEM_HW_MACADDR(AGENT_REQUEST *request, AGENT_RESULT *result);
int	SYSTEM_SW_OS(AGENT_REQUEST *request, AGENT_RESULT *result);
int	SYSTEM_SW_PACKAGES(AGENT_REQUEST *request, AGENT_RESULT *result);
int	SYSTEM_SWAP_IN(AGENT_REQUEST *request, AGENT_RESULT *result);
int	SYSTEM_SWAP_OUT(AGENT_REQUEST *request, AGENT_RESULT *result);
int	SYSTEM_SWAP_SIZE(AGENT_REQUEST *request, AGENT_RESULT *result);
int	SYSTEM_USERS_NUM(AGENT_REQUEST *request, AGENT_RESULT *result);
int	VFS_DIR_COUNT(AGENT_REQUEST *request, AGENT_RESULT *result);
int	VFS_DIR_SIZE(AGENT_REQUEST *request, AGENT_RESULT *result);
int	VFS_FILE_MD5SUM(AGENT_REQUEST *request, AGENT_RESULT *result);
int	VFS_FILE_REGMATCH(AGENT_REQUEST *request, AGENT_RESULT *result);
int	VFS_FS_DISCOVERY(AGENT_REQUEST *request, AGENT_RESULT *result);
int	VFS_FS_INODE(AGENT_REQUEST *request, AGENT_RESULT *result);
int	VFS_FS_SIZE(AGENT_REQUEST *request, AGENT_RESULT *result);
int	VM_MEMORY_SIZE(AGENT_REQUEST *request, AGENT_RESULT *result);

static int execute_check(const char *key, zbx_agent_check_t check_func, char **value, char **error)
{
	int ret = FAIL;
	char **pvalue;
	AGENT_RESULT result;
	AGENT_REQUEST request;

	init_request(&request);
	init_result(&result);
	if (SUCCEED != parse_item_key(key, &request))
	{
		*value = zbx_strdup(NULL, "Invalid item key format.");
		goto out;
	}
	if (SYSINFO_RET_OK != check_func(&request, &result))
	{
		if (0 != ISSET_MSG(&result))
		{
			*error = zbx_strdup(NULL, result.msg);
		}
		else
			*error = zbx_strdup(NULL, "Unknown error.");
		goto out;
	}

	if (NULL != (pvalue = GET_TEXT_RESULT(&result)))
		*value = zbx_strdup(NULL, *pvalue);

	ret = SUCCEED;
out:
	free_result(&result);
	free_request(&request);
	return ret;
}

*/
import "C"

import (
	"errors"
	"fmt"
	"unsafe"
	"zabbix/pkg/itemutil"
)

func ExecuteCheck(key string, params []string) (result *string, err error) {
	cfunc := resolveMetric(key)

	if cfunc == nil {
		return nil, fmt.Errorf("Unsupported metric %s", key)
	}

	var cvalue, cerrmsg *C.char
	ckey := C.CString(itemutil.MakeKey(key, params))
	if C.execute_check(ckey, C.zbx_agent_check_t(cfunc), &cvalue, &cerrmsg) == Succeed {
		if cvalue != nil {
			value := C.GoString(cvalue)
			result = &value
		}
		C.free(unsafe.Pointer(cvalue))

	} else {
		err = errors.New(C.GoString(cerrmsg))
		C.free(unsafe.Pointer(cerrmsg))
	}
	C.free(unsafe.Pointer(ckey))
	return
}
