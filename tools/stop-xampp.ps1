$ErrorActionPreference = 'Stop'
$siteRoot = [IO.Path]::GetFullPath((Join-Path $PSScriptRoot '..'))
$configFile = Join-Path $siteRoot 'storage\framework\xampp\httpd.conf'
$pidFile = Join-Path $siteRoot 'storage\framework\xampp-httpd.pid'
if (-not (Test-Path -LiteralPath $pidFile)) { Write-Output 'Project Apache is not running.'; exit 0 }
$instanceId = [int](Get-Content -LiteralPath $pidFile)
$instance = Get-CimInstance Win32_Process -Filter "ProcessId = $instanceId"
if (-not $instance -or $instance.Name -ne 'httpd.exe' -or -not $instance.CommandLine.Contains($configFile)) {
    throw 'Process identity does not match this project. No process was stopped.'
}
$children = Get-CimInstance Win32_Process -Filter "ParentProcessId = $instanceId"
foreach ($child in $children) {
    if ($child.Name -eq 'httpd.exe') { Stop-Process -Id $child.ProcessId -ErrorAction SilentlyContinue }
}
Stop-Process -Id $instanceId -ErrorAction SilentlyContinue
Write-Output 'Stopped this project Apache instance.'
