#!/bin/bash
set -e

# Cores
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

print_success() { echo -e "${GREEN}✅ $1${NC}"; }
print_error() { echo -e "${RED}❌ $1${NC}"; }
print_warning() { echo -e "${YELLOW}⚠️  $1${NC}"; }
print_info() { echo -e "${BLUE}ℹ️  $1${NC}"; }
print_header() {
    echo ""
    echo -e "${BLUE}================================${NC}"
    echo -e "${BLUE}$1${NC}"
    echo -e "${BLUE}================================${NC}"
    echo ""
}

if [ ! -f "docker-compose.yml" ]; then
    print_error "docker-compose.yml não encontrado!"
    exit 1
fi

print_header "🚀 MIGRAÇÃO DO PROJETO"

# CORREÇÃO: Aspas para lidar com espaços
CURRENT_DIR=$(basename "$(pwd)")
PARENT_DIR=$(dirname "$(pwd)")
BACKUP_NAME="${CURRENT_DIR}-backup"

print_header "📦 Criando Backup"
if [ -d "${PARENT_DIR}/${BACKUP_NAME}" ]; then
    print_warning "Removendo backup anterior..."
    rm -rf "${PARENT_DIR}/${BACKUP_NAME}"
fi

print_info "Criando backup..."
cd "${PARENT_DIR}"
cp -r "${CURRENT_DIR}" "${BACKUP_NAME}"
cd "${CURRENT_DIR}"
print_success "Backup criado!"

print_header "🛑 Parando Containers"
docker-compose down 2>/dev/null || print_info "Nenhum container rodando"

print_header "📁 Criando Estrutura"
mkdir -p docker config/database public/css public/js docs
print_success "Pastas criadas!"

print_header "🐳 Organizando Docker"
[ -f "Dockerfile" ] && mv Dockerfile docker/Dockerfile.app && print_success "Dockerfile movido"
[ -f "Dockerfile.jenkins" ] && mv Dockerfile.jenkins docker/ && print_success "Dockerfile.jenkins movido"
[ -f "Jenkinsfile" ] || [ -f "jenkinsfile" ] && mv [Jj]enkinsfile docker/Jenkinsfile 2>/dev/null && print_success "Jenkinsfile movido"

print_header "⚙️  Organizando Configs"
[ -f "init.sql" ] && mv init.sql config/database/ && print_success "init.sql movido"
[ -f "CriarBanco.txt" ] && mv CriarBanco.txt docs/ && print_success "CriarBanco.txt movido"

print_header "🌐 Organizando Public"
for f in index.php login.php register.php processa_carro.php; do
    [ -f "$f" ] && mv "$f" public/ && print_success "$f movido"
done
[ -d "css" ] && mv css public/ 2>/dev/null && print_success "css/ movido"
[ -d "js" ] && mv js public/ 2>/dev/null && print_success "js/ movido"

print_header "✅ Concluído!"
print_success "🎉 Migração finalizada!"
print_warning "📌 Backup em: ${PARENT_DIR}/${BACKUP_NAME}"
print_info "Próximo passo: Atualize docker-compose.yml e execute:"
print_info "docker-compose up -d --build"