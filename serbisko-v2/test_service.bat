@echo off
REM Simple PowerShell script to test the enrollment service
powershell -Command "
$url = 'http://localhost:5002/status'
Write-Host 'Testing service on ' $url
try {
    $response = Invoke-WebRequest -Uri $url -Method GET -TimeoutSec 5
    Write-Host 'Status: SUCCESS'
    Write-Host 'Response:' $response.Content
} catch {
    Write-Host 'Status: FAILED'
    Write-Host 'Error:' $_
}
"
pause
