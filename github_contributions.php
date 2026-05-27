<?php
/**
 * GitHub贡献图数据获取脚本
 * 功能：从GitHub API获取用户贡献数据，缓存到本地文件，前端通过此接口获取数据
 */

// ======= 配置区 =======
$github_username = "LucasAndrew0120";  // GitHub用户名
$cache_file = __DIR__ . '/github_contributions_cache.json';  // 缓存文件路径
$cache_time = 7200;  // 缓存时间（秒），2小时

// GitHub个人访问令牌（可选，用于提高API限制）
// 如果需要，请取消注释并填写你的token
// $github_token = "your_github_personal_access_token_here";

// ======= 主函数：获取贡献数据 =======
function getGitHubContributions($username, $cache_file, $cache_time) {
    // 检查缓存是否有效
    if (file_exists($cache_file) && (time() - filemtime($cache_file)) < $cache_time) {
        $cached_data = file_get_contents($cache_file);
        $data = json_decode($cached_data, true);
        if ($data && isset($data['contributions'])) {
            return $data;
        }
    }
    
    // 缓存无效或不存在，从GitHub API获取数据
    $contributions_data = fetchFromGitHubAPI($username);
    
    if ($contributions_data) {
        // 保存到缓存
        $cache_data = [
            'contributions' => $contributions_data,
            'last_updated' => date('Y-m-d H:i:s'),
            'username' => $username
        ];
        file_put_contents($cache_file, json_encode($cache_data, JSON_PRETTY_PRINT));
        return $cache_data;
    }
    
    // 如果API请求失败，尝试返回缓存（即使过期）
    if (file_exists($cache_file)) {
        $cached_data = file_get_contents($cache_file);
        $data = json_decode($cached_data, true);
        if ($data && isset($data['contributions'])) {
            return $data;
        }
    }
    
    // 全部失败，返回空数据
    return [
        'contributions' => [],
        'last_updated' => date('Y-m-d H:i:s'),
        'username' => $username,
        'error' => '无法获取GitHub贡献数据'
    ];
}

// ======= 从GitHub API获取数据 =======
function fetchFromGitHubAPI($username) {
    // 方法1：使用GitHub的GraphQL API（推荐，可以获取更详细的数据）
    $query = '
    {
      user(login: "' . $username . '") {
        contributionsCollection {
          contributionCalendar {
            totalContributions
            weeks {
              contributionDays {
                contributionCount
                date
                weekday
              }
            }
          }
        }
      }
    }';
    
    $data = makeGitHubGraphQLRequest($query);
    
    if ($data && isset($data['data']['user']['contributionsCollection']['contributionCalendar'])) {
        $calendar = $data['data']['user']['contributionsCollection']['contributionCalendar'];
        $weeks = $calendar['weeks'];
        
        $contributions = [];
        foreach ($weeks as $week) {
            foreach ($week['contributionDays'] as $day) {
                $contributions[] = [
                    'date' => $day['date'],
                    'count' => $day['contributionCount'],
                    'weekday' => $day['weekday']
                ];
            }
        }
        
        return [
            'total' => $calendar['totalContributions'],
            'daily' => $contributions,
            'weeks' => count($weeks)
        ];
    }
    
    // 方法2：如果GraphQL失败，尝试使用REST API（获取最近事件）
    return fetchFromGitHubEventsAPI($username);
}

// ======= 使用GraphQL API请求 =======
function makeGitHubGraphQLRequest($query) {
    $url = 'https://api.github.com/graphql';
    
    $headers = [
        'Content-Type: application/json',
        'User-Agent: PHP-GitHub-Contributions-Fetcher'
    ];
    
    // 如果有token，添加认证头
    global $github_token;
    if (isset($github_token) && !empty($github_token)) {
        $headers[] = 'Authorization: bearer ' . $github_token;
    }
    
    $data = json_encode(['query' => $query]);
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    
    // 对于GitHub API，需要设置一些安全选项
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($http_code == 200) {
        return json_decode($response, true);
    }
    
    // 记录错误
    error_log("GitHub GraphQL API请求失败，HTTP代码: " . $http_code);
    return null;
}

// ======= 使用REST API获取事件数据（备用方案） =======
function fetchFromGitHubEventsAPI($username) {
    $url = "https://api.github.com/users/{$username}/events/public?per_page=100";
    
    $headers = [
        'User-Agent: PHP-GitHub-Contributions-Fetcher'
    ];
    
    // 如果有token，添加认证头
    global $github_token;
    if (isset($github_token) && !empty($github_token)) {
        $headers[] = 'Authorization: bearer ' . $github_token;
    }
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($http_code == 200) {
        $events = json_decode($response, true);
        
        // 统计最近30天的贡献
        $contributions = [];
        $thirty_days_ago = date('Y-m-d', strtotime('-30 days'));
        
        foreach ($events as $event) {
            if (isset($event['created_at'])) {
                $date = date('Y-m-d', strtotime($event['created_at']));
                if ($date >= $thirty_days_ago) {
                    if (!isset($contributions[$date])) {
                        $contributions[$date] = 0;
                    }
                    $contributions[$date]++;
                }
            }
        }
        
        // 转换为前端需要的格式
        $daily_contributions = [];
        foreach ($contributions as $date => $count) {
            $daily_contributions[] = [
                'date' => $date,
                'count' => $count,
                'weekday' => date('w', strtotime($date))
            ];
        }
        
        // 按日期排序
        usort($daily_contributions, function($a, $b) {
            return strcmp($a['date'], $b['date']);
        });
        
        return [
            'total' => array_sum($contributions),
            'daily' => $daily_contributions,
            'weeks' => 5, // 大约5周
            'note' => '基于最近30天的事件数据'
        ];
    }
    
    return null;
}

// ======= 生成贡献图SVG（改进版：最近30天，黑色背景，绿色贡献块） =======
function generateContributionsSVG($contributions_data) {
    if (empty($contributions_data) || !isset($contributions_data['daily'])) {
        return '';
    }
    
    $daily_data = $contributions_data['daily'];
    
    // 确定日期范围（最近30天）
    $end_date = date('Y-m-d');
    $start_date = date('Y-m-d', strtotime('-29 days')); // 30天包括今天
    
    // 创建日期到贡献数的映射
    $contributions_map = [];
    foreach ($daily_data as $day) {
        $contributions_map[$day['date']] = $day['count'];
    }
    
    // 生成SVG - 根据数据动态计算尺寸
    $cell_size = 14;
    $cell_margin = 3;
    $left_padding = 35;
    $top_padding = 45;
    $right_padding = 15;
    $bottom_padding = 15;
    
    // 计算周数
    $total_days = (strtotime($today) - strtotime($start_date)) / 86400 + 1;
    $total_weeks = ceil($total_days / 7);
    
    $svg_width = $left_padding + $total_weeks * ($cell_size + $cell_margin) + $right_padding;
    $svg_height = $top_padding + 7 * ($cell_size + $cell_margin) + $bottom_padding;
    
    $svg = '<svg width="' . $svg_width . '" height="' . $svg_height . '" viewBox="0 0 ' . $svg_width . ' ' . $svg_height . '" xmlns="http://www.w3.org/2000/svg">';
    $svg .= '<style>
        .contrib-cell { 
            rx: 4; 
            ry: 4; 
            transition: stroke 0.2s ease;
            cursor: pointer;
        }
        .contrib-cell:hover {
            stroke: #64ffda;
            stroke-width: 2px;
            /* 移除transform: scale(1.05); 和 filter: brightness(1.2); */
        }
        .contrib-text {
            font-family: "LXGW WenKai Screen", Arial, sans-serif;
            font-size: 12px;
            fill: #ddd;
        }
        .tooltip {
            font-family: "LXGW WenKai Screen", Arial, sans-serif;
            font-size: 12px;
            fill: white;
        }
        .month-label {
            font-family: "LXGW WenKai Screen", Arial, sans-serif;
            font-size: 11px;
            fill: #aaa;
            font-weight: 500;
        }
        .day-label {
            font-family: "LXGW WenKai Screen", Arial, sans-serif;
            font-size: 10px;
            fill: #999;
        }
        .legend-label {
            font-family: "LXGW WenKai Screen", Arial, sans-serif;
            font-size: 10px;
            fill: #bbb;
        }
    </style>';
    
    // 添加星期标签（左侧）
    $weekdays = ['日', '一', '二', '三', '四', '五', '六'];
    for ($i = 0; $i < 7; $i++) {
        $y = $top_padding + $i * ($cell_size + $cell_margin) + $cell_size / 2 + 3;
        $svg .= '<text x="' . ($left_padding - 8) . '" y="' . $y . '" class="day-label" text-anchor="end">' . $weekdays[$i] . '</text>';
    }
    
    // 生成贡献格子（最近30天，按周排列）
    $current_date = $start_date;
    $today = date('Y-m-d');
    $day_index = 0;
    $last_month_label_x = -100; // 记录上一个月标签的x位置，防止重叠
    
    while (strtotime($current_date) <= strtotime($today)) {
        $count = isset($contributions_map[$current_date]) ? $contributions_map[$current_date] : 0;
        
        // 根据贡献数确定绿色深浅
        if ($count == 0) {
            $color = '#1a1a1a';
        } elseif ($count <= 2) {
            $color = '#0d5c1a';
        } elseif ($count <= 5) {
            $color = '#1a7d2e';
        } elseif ($count <= 10) {
            $color = '#2ebf4f';
        } else {
            $color = '#4aff7a';
        }
        
        // 计算位置（按周排列）
        $week_day = date('w', strtotime($current_date));
        $week_num = floor($day_index / 7);
        
        $pos_x = $left_padding + $week_num * ($cell_size + $cell_margin);
        $pos_y = $top_padding + $week_day * ($cell_size + $cell_margin);
        
        // 格式化日期显示
        $display_date = date('n月j日', strtotime($current_date));
        $display_day = date('D', strtotime($current_date));
        
        $svg .= '<rect class="contrib-cell" x="' . $pos_x . '" y="' . $pos_y . '" 
                width="' . $cell_size . '" height="' . $cell_size . '" 
                fill="' . $color . '" 
                data-count="' . $count . '" 
                data-date="' . $current_date . '"
                data-display-date="' . $display_date . '"
                data-display-day="' . $display_day . '">
                <title>' . $display_date . ' (' . $weekdays[$week_day] . '): ' . $count . ' 次提交</title>
            </rect>';
        
        // 如果是每月的第一天，添加月份标签（防止重叠）
        if (date('j', strtotime($current_date)) == 1 || $day_index == 0) {
            $label_x = $pos_x + $cell_size / 2;
            if ($label_x - $last_month_label_x >= 30) {
                $month = date('n月', strtotime($current_date));
                $svg .= '<text x="' . $label_x . '" y="30" class="month-label" text-anchor="middle">' . $month . '</text>';
                $last_month_label_x = $label_x;
            }
        }
        
        $current_date = date('Y-m-d', strtotime($current_date . ' +1 day'));
        $day_index++;
    }
    
    // 移除标题和图例，只保留贡献格子
    
    // 添加鼠标悬停提示区域
    $svg .= '<rect id="tooltip-bg" x="0" y="0" width="180" height="60" fill="rgba(10, 10, 10, 0.95)" rx="8" ry="8" 
            stroke="#64ffda" stroke-width="2" visibility="hidden"/>
            <text id="tooltip-date" x="12" y="25" class="tooltip" visibility="hidden" font-size="12">日期: </text>
            <text id="tooltip-count" x="12" y="45" class="tooltip" visibility="hidden" font-size="12">提交次数: </text>';
    
    // 添加JavaScript交互
    $svg .= '<script type="application/ecmascript"><![CDATA[
        const svg = document.querySelector("svg");
        const tooltipBg = document.getElementById("tooltip-bg");
        const tooltipDate = document.getElementById("tooltip-date");
        const tooltipCount = document.getElementById("tooltip-count");
        
        const cells = document.querySelectorAll(".contrib-cell");
        
        cells.forEach(cell => {
            cell.addEventListener("mouseenter", function(e) {
                const rect = this.getBoundingClientRect();
                const svgRect = svg.getBoundingClientRect();
                
                const date = this.getAttribute("data-display-date") || this.getAttribute("data-date");
                const count = this.getAttribute("data-count");
                const day = this.getAttribute("data-display-day") || "";
                
                tooltipDate.textContent = date + " (" + day + ")";
                tooltipCount.textContent = count + " 次提交";
                
                let x = rect.left - svgRect.left + rect.width/2 - 90;
                let y = rect.top - svgRect.top - 65;
                
                if (y < 5) y = rect.bottom - svgRect.top + 8;
                if (x > svgRect.width - 170) x = svgRect.width - 170;
                if (x < 5) x = 5;
                
                tooltipBg.setAttribute("x", x);
                tooltipBg.setAttribute("y", y);
                tooltipDate.setAttribute("x", x + 12);
                tooltipDate.setAttribute("y", y + 22);
                tooltipCount.setAttribute("x", x + 12);
                tooltipCount.setAttribute("y", y + 42);
                
                tooltipBg.setAttribute("visibility", "visible");
                tooltipDate.setAttribute("visibility", "visible");
                tooltipCount.setAttribute("visibility", "visible");
            });
            
            cell.addEventListener("mouseleave", function() {
                tooltipBg.setAttribute("visibility", "hidden");
                tooltipDate.setAttribute("visibility", "hidden");
                tooltipCount.setAttribute("visibility", "hidden");
            });
        });
    ]]></script>';
    
    $svg .= '</svg>';
    
    return $svg;
}

// ======= 主程序 =======
// 设置响应头
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// 获取贡献数据
$contributions_data = getGitHubContributions($github_username, $cache_file, $cache_time);

// 如果需要SVG，可以生成
if (isset($_GET['format']) && $_GET['format'] === 'svg') {
    header('Content-Type: image/svg+xml');
    echo generateContributionsSVG($contributions_data['contributions']);
    exit;
}

// 返回JSON数据
echo json_encode($contributions_data, JSON_PRETTY_PRINT);
?>
