<?php
/**
 * 个人面板状态抓取脚本 - 稳定版
 * 功能：自动计算月付续费倒计时、读取系统真实负载、内存、网速及运行时间
 */

// ======= 基础配置区 =======
$pay_day = 27;           // [必填] 你每月的续费日期 (1-31)
$network_card = "eth0"; // [必填] 网卡名。若流量为0，请在 1Panel 终端输入 ip addr 查看并修改（常见: eth0, ens3, venet0）

// --- 1. 自动计算本月续费倒计时 ---
$today = new DateTime();
$current_day = (int)$today->format('j');
$target_date = new DateTime();

if ($current_day < $pay_day) {
    // 还没到本月续费日
    $target_date->setDate((int)$today->format('Y'), (int)$today->format('m'), $pay_day);
} else {
    // 已过本月续费日，算下个月
    $target_date->modify('+1 month');
    $target_date->setDate((int)$target_date->format('Y'), (int)$target_date->format('m'), $pay_day);
}
$days_left = $today->diff($target_date)->days;

// --- 2. 获取 CPU 负载 (读取 /proc/loadavg) ---
$cpuUsage = 0;
if (function_exists('sys_getloadavg')) {
    $load = sys_getloadavg();
    // 假设是 2 核服务器，除以 2 得到百分比；1 核请去掉 / 2
    $cpuUsage = round($load[0] * 100 / 2, 1); 
}
if ($cpuUsage > 100) $cpuUsage = 100;

// --- 3. 获取内存信息 (执行 free 命令) ---
$memUsage = 0;
$free = @shell_exec('free');
if ($free) {
    $free_arr = explode("\n", (string)trim($free));
    $mem_all = array_values(array_filter(explode(" ", $free_arr[1])));
    if (isset($mem_all[2]) && isset($mem_all[1])) {
        $memUsage = round($mem_all[2] / $mem_all[1] * 100, 1);
    }
}

// --- 4. 获取磁盘占用 ---
$diskUsage = round((1 - (@disk_free_space("/") / @disk_total_space("/"))) * 100, 1);

// --- 5. 获取实时网速 (读取网卡统计文件) ---
$net_in = 0; $net_out = 0;
$rx_path = "/sys/class/net/$network_card/statistics/rx_bytes";
$tx_path = "/sys/class/net/$network_card/statistics/tx_bytes";

if (file_exists($rx_path)) {
    $rx1 = file_get_contents($rx_path);
    $tx1 = file_get_contents($tx_path);
    sleep(1); // 间隔一秒
    $rx2 = file_get_contents($rx_path);
    $tx2 = file_get_contents($tx_path);
    $net_in = round(($rx2 - $rx1) / 1024, 1);  // KB/s
    $net_out = round(($tx2 - $tx1) / 1024, 1); // KB/s
}

// --- 6. 获取运行时间 (读取 /proc/uptime，最稳妥方案) ---
$uptime = "获取中...";
$uptime_data = @file_get_contents('/proc/uptime');
if ($uptime_data) {
    $uptime_seconds = (int)explode(' ', $uptime_data)[0];
    $d = floor($uptime_seconds / 86400);
    $h = floor(($uptime_seconds % 86400) / 3600);
    $m = floor(($uptime_seconds % 3600) / 60);
    
    if ($d > 0) {
        $uptime = "{$d}天 {$h}小时 {$m}分";
    } else {
        $uptime = "{$h}小时 {$m}分";
    }
}

// ======= 输出 JSON 数据 =======
header('Content-Type: application/json');
echo json_encode([
    'cpu' => $cpuUsage,
    'mem' => $memUsage,
    'disk' => $diskUsage,
    'net_in' => $net_in,
    'net_out' => $net_out,
    'uptime' => $uptime,
    'days_left' => $days_left
]);