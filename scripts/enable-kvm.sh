#!/bin/bash

# Script para habilitar KVM no Linux
# Execute com: bash scripts/enable-kvm.sh

set -e

echo "🔍 Verificando suporte de virtualização..."

# Verificar se CPU suporta virtualização
if grep -qE 'vmx|svm' /proc/cpuinfo; then
    echo "✅ Hardware suporta virtualização"
    if grep -q vmx /proc/cpuinfo; then
        echo "   Tipo: Intel VT-x"
        KVM_MODULE="kvm_intel"
    elif grep -q svm /proc/cpuinfo; then
        echo "   Tipo: AMD-V"
        KVM_MODULE="kvm_amd"
    fi
else
    echo "❌ Hardware NÃO suporta virtualização"
    echo "   Você precisa habilitar na BIOS/UEFI primeiro"
    exit 1
fi

echo ""
echo "📦 Verificando módulos KVM..."

# Verificar se módulos estão carregados
if lsmod | grep -q kvm; then
    echo "✅ Módulos KVM já estão carregados"
else
    echo "⚠️  Módulos KVM não estão carregados"
    echo "   Carregando módulos..."
    
    sudo modprobe kvm
    sudo modprobe $KVM_MODULE
    
    if lsmod | grep -q kvm; then
        echo "✅ Módulos KVM carregados com sucesso"
    else
        echo "❌ Falha ao carregar módulos KVM"
        exit 1
    fi
fi

echo ""
echo "🔧 Verificando /dev/kvm..."

# Verificar se /dev/kvm existe
if [ -e /dev/kvm ]; then
    echo "✅ /dev/kvm existe"
    ls -l /dev/kvm
else
    echo "❌ /dev/kvm não existe"
    echo "   Isso pode indicar que os módulos não foram carregados corretamente"
    exit 1
fi

echo ""
echo "👤 Verificando grupos do usuário..."

# Verificar grupos
if groups | grep -q kvm; then
    echo "✅ Usuário está no grupo 'kvm'"
else
    echo "⚠️  Usuário NÃO está no grupo 'kvm'"
    echo "   Execute: sudo usermod -aG kvm $USER"
    echo "   Depois faça logout/login ou reinicie"
fi

if groups | grep -q libvirt; then
    echo "✅ Usuário está no grupo 'libvirt'"
else
    echo "⚠️  Usuário NÃO está no grupo 'libvirt'"
    echo "   Execute: sudo usermod -aG libvirt $USER"
fi

echo ""
echo "🔒 Verificando permissões de /dev/kvm..."

# Verificar permissões
if [ -r /dev/kvm ] && [ -w /dev/kvm ]; then
    echo "✅ Permissões de /dev/kvm estão corretas"
else
    echo "⚠️  Permissões de /dev/kvm podem estar incorretas"
    echo "   Tentando corrigir..."
    sudo chown root:kvm /dev/kvm
    sudo chmod 0660 /dev/kvm
    
    # Criar regra udev permanente
    if [ ! -f /etc/udev/rules.d/99-kvm.rules ]; then
        echo "   Criando regra udev permanente..."
        echo 'KERNEL=="kvm", GROUP="kvm", MODE="0666"' | sudo tee /etc/udev/rules.d/99-kvm.rules > /dev/null
        sudo udevadm control --reload-rules
        sudo udevadm trigger
    fi
fi

echo ""
echo "📋 Verificando se módulos são carregados automaticamente..."

# Verificar se módulos são carregados automaticamente
if [ -f /etc/modules-load.d/kvm.conf ]; then
    echo "✅ Configuração de auto-carregamento existe"
    cat /etc/modules-load.d/kvm.conf
else
    echo "⚠️  Configuração de auto-carregamento não existe"
    echo "   Criando configuração..."
    echo "kvm" | sudo tee /etc/modules-load.d/kvm.conf > /dev/null
    echo "$KVM_MODULE" | sudo tee -a /etc/modules-load.d/kvm.conf > /dev/null
    echo "✅ Configuração criada"
fi

echo ""
echo "🧪 Testando KVM..."

# Testar com virt-host-validate se disponível
if command -v virt-host-validate &> /dev/null; then
    echo "Executando virt-host-validate..."
    sudo virt-host-validate qemu 2>&1 | grep -E "PASS|FAIL|WARN" || true
else
    echo "⚠️  virt-host-validate não está instalado"
    echo "   Para instalar: sudo apt install libvirt-clients"
fi

echo ""
echo "✅ Verificação completa!"
echo ""
echo "📝 Próximos passos:"
echo "   1. Se você não está nos grupos kvm/libvirt, execute:"
echo "      sudo usermod -aG kvm,libvirt $USER"
echo "      Depois faça logout/login ou reinicie"
echo ""
echo "   2. Se /dev/kvm ainda não existe após carregar módulos,"
echo "      verifique se a virtualização está habilitada na BIOS"
echo ""
echo "   3. Reinicie o Docker Desktop após essas mudanças"
echo ""
echo "   4. Alternativamente, use Docker Engine ao invés de Docker Desktop:"
echo "      sudo apt install docker.io docker-compose"
echo "      sudo usermod -aG docker $USER"
echo "      newgrp docker"

