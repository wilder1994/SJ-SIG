# Abre TCP 8086 en perfiles Privado/Dominio para que otros equipos de la LAN
# puedan entrar a SJ-SIG. Ejecutar en PowerShell como Administrador:
#   Set-ExecutionPolicy -Scope Process Bypass
#   .\docs\apache\abrir-firewall-8086.ps1

$ErrorActionPreference = 'Stop'
$name = 'SJ-SIG LAN 8086'

$existing = Get-NetFirewallRule -DisplayName $name -ErrorAction SilentlyContinue
if ($existing) {
    Set-NetFirewallRule -DisplayName $name -Enabled True -Profile Private,Domain -Action Allow
    Write-Host "Regla actualizada: $name"
} else {
    New-NetFirewallRule `
        -DisplayName $name `
        -Direction Inbound `
        -Protocol TCP `
        -LocalPort 8086 `
        -Action Allow `
        -Profile Private,Domain `
        -Description 'Acceso LAN a SJ-SIG (Apache Laragon puerto 8086)'
    Write-Host "Regla creada: $name"
}

Write-Host ''
Write-Host 'IPs IPv4 de este equipo (usar http://IP:8086/ingreso desde la red):'
Get-NetIPAddress -AddressFamily IPv4 |
    Where-Object { $_.IPAddress -notlike '127.*' -and $_.AddressState -eq 'Preferred' } |
    ForEach-Object { Write-Host ("  http://{0}:8086/ingreso  ({1})" -f $_.IPAddress, $_.InterfaceAlias) }
