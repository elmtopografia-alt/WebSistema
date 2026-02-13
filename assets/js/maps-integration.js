/**
 * SGT MapIntegration - Integração com OpenStreetMap/Leaflet
 * Solução 100% Gratuita para Geocodificação e Rotas
 */

const MapIntegration = {
    map: null,
    markerOrigem: null,
    markerDestino: null,
    routeLayer: null,

    // Configurações Padrão
    config: {
        sedeCoords: null, // Será preenchido na init
        custoKm: 0.85,
        custoPedagioKm: 0.12 // Estimativa média de pedágio por km em rodovias
    },

    init() {
        this.bindAutocomplete();
        console.log('🗺️ MapIntegration Inicializado');
    },

    /**
     * Autocomplete de Endereço usando Photon (API gratuita baseada em Nominatim)
     */
    bindAutocomplete() {
        const input = document.getElementById('endereco_obra');
        const suggestionBox = document.getElementById('address-suggestions');
        if (!input || !suggestionBox) return;

        input.addEventListener('input', SGTUtils.debounce(async (e) => {
            const query = e.target.value;
            if (query.length < 3) {
                suggestionBox.style.display = 'none';
                return;
            }

            try {
                // Photon API é rápida e não precisa de key
                const response = await fetch(`https://photon.komoot.io/api/?q=${encodeURIComponent(query)}&limit=5&lang=pt`);
                const data = await response.json();

                this.renderSuggestions(data.features);
            } catch (err) {
                console.error('Erro autocomplete:', err);
            }
        }, 400));

        // Fecha sugestões ao clicar fora
        document.addEventListener('click', (e) => {
            if (!suggestionBox.contains(e.target) && e.target !== input) {
                suggestionBox.style.display = 'none';
            }
        });
    },

    renderSuggestions(features) {
        const suggestionBox = document.getElementById('address-suggestions');
        suggestionBox.innerHTML = '';

        if (!features.length) {
            suggestionBox.style.display = 'none';
            return;
        }

        features.forEach(f => {
            const props = f.properties;
            const label = [
                props.name || props.street,
                props.housenumber,
                props.district || props.city,
                props.state,
                props.country
            ].filter(Boolean).join(', ');

            const div = document.createElement('div');
            div.className = 'suggestion-item';
            div.textContent = label;
            div.onclick = () => this.selectAddress(f, label);
            suggestionBox.appendChild(div);
        });

        suggestionBox.style.display = 'block';
    },

    selectAddress(feature, label) {
        const props = feature.properties;
        const coords = feature.geometry.coordinates; // [lng, lat]

        document.getElementById('endereco_obra').value = label;
        document.getElementById('address-suggestions').style.display = 'none';

        // Preenche campos detalhados
        document.getElementById('endereco_rua').value = (props.street || props.name || '');
        document.getElementById('bairro').value = props.district || '';
        document.getElementById('cidade').value = props.city || '';
        if (props.state) {
            // Tenta selecionar o estado no dropdown por sigla ou nome
            const stateSelect = document.getElementById('estado');
            if (stateSelect) {
                // Se for sigla (2 letras)
                if (props.state.length === 2) {
                    stateSelect.value = props.state.toUpperCase();
                } else {
                    // Busca opção que contenha o nome do estado
                    Array.from(stateSelect.options).forEach(opt => {
                        if (opt.text.toLowerCase().includes(props.state.toLowerCase())) {
                            stateSelect.value = opt.value;
                        }
                    });
                }
            }
        }

        // Salva coordenadas
        document.getElementById('geo_lat').value = coords[1];
        document.getElementById('geo_lng').value = coords[0];

        SGTUtils.showToast('Endereço selecionado! Clique no ícone de mapa para roteirizar.', 'success');
    },

    async openModal() {
        const modal = document.getElementById('mapModal');
        if (!modal) return;

        modal.style.display = 'flex';

        // Inicializa mapa se não existir
        if (!this.map) {
            this.initLeaflet();
        }

        // Tenta buscar sede se ainda não tiver
        if (!this.config.sedeCoords) {
            await this.geocodeSede();
        }

        // Se o input de endereço tem texto mas não temos lat/lng, tenta geocodificar o destino agora
        const enderecoObra = document.getElementById('endereco_obra').value;
        const latInput = document.getElementById('geo_lat').value;
        const lngInput = document.getElementById('geo_lng').value;

        if (enderecoObra && (!latInput || !lngInput)) {
            SGTUtils.showToast('Localizando obra...', 'info');
            try {
                const resp = await fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(enderecoObra)}&limit=1`);
                const data = await resp.json();
                if (data && data.length) {
                    document.getElementById('geo_lat').value = data[0].lat;
                    document.getElementById('geo_lng').value = data[0].lon;
                    if (this.markerDestino) {
                        this.markerDestino.setLatLng([data[0].lat, data[0].lon]);
                    }
                }
            } catch (err) {
                console.error('Erro ao geocodificar obra ao abrir modal:', err);
            }
        }

        this.updateMapFromInputs();
    },

    closeModal() {
        document.getElementById('mapModal').style.display = 'none';
    },

    initLeaflet() {
        this.map = L.map('map-canvas').setView([-19.9167, -43.9345], 13); // Default BH

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors'
        }).addTo(this.map);

        // Ícones Customizados
        const IconSede = L.icon({
            iconUrl: 'https://cdn.rawgit.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-orange.png',
            shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
            iconSize: [25, 41],
            iconAnchor: [12, 41],
            popupAnchor: [1, -34],
            shadowSize: [41, 41]
        });

        const IconObra = L.icon({
            iconUrl: 'https://cdn.rawgit.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-blue.png',
            shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
            iconSize: [25, 41],
            iconAnchor: [12, 41],
            popupAnchor: [1, -34],
            shadowSize: [41, 41]
        });

        this.markerOrigem = L.marker([0, 0], { icon: IconSede }).addTo(this.map).bindPopup('Sede (Origem)');
        this.markerDestino = L.marker([0, 0], { icon: IconObra, draggable: true }).addTo(this.map).bindPopup('Obra (Arraste para ajustar)');

        this.markerDestino.on('dragend', () => {
            const pos = this.markerDestino.getLatLng();
            document.getElementById('geo_lat').value = pos.lat.toFixed(6);
            document.getElementById('geo_lng').value = pos.lng.toFixed(6);
            this.calculateRoute();
            this.reverseGeocode(pos.lat, pos.lng);
        });
    },

    async geocodeSede() {
        const enderecoSede = window.SGT_DATA?.enderecoEmpresa;
        if (!enderecoSede) {
            console.warn('Endereço da sede não configurado em SGT_DATA.');
            // Fallback para BH se falhar tudo
            this.config.sedeCoords = [-19.9167, -43.9345];
            return;
        }

        try {
            // Adicionando um User-Agent e parâmetros extras para melhorar a busca no Nominatim
            const resp = await fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(enderecoSede)}&limit=1&addressdetails=1`, {
                headers: { 'Accept-Language': 'pt-BR' }
            });
            const data = await resp.json();
            if (data.length) {
                this.config.sedeCoords = [parseFloat(data[0].lat), parseFloat(data[0].lon)];
                console.log('✅ Sede localizada:', this.config.sedeCoords);
            } else {
                console.warn('Sede não encontrada no Nominatim, usando fallback.');
                this.config.sedeCoords = [-19.9167, -43.9345];
            }
        } catch (err) {
            console.error('Erro geocode sede:', err);
            this.config.sedeCoords = [-19.9167, -43.9345];
        }
    },

    updateMapFromInputs() {
        const lat = parseFloat(document.getElementById('geo_lat').value);
        const lng = parseFloat(document.getElementById('geo_lng').value);

        if (this.config.sedeCoords) {
            this.markerOrigem.setLatLng(this.config.sedeCoords);
        }

        if (lat && lng) {
            this.markerDestino.setLatLng([lat, lng]);
            this.calculateRoute();
        } else {
            // Se não tem destino, foca na sede
            if (this.config.sedeCoords) {
                this.map.setView(this.config.sedeCoords, 12);
            }
        }

        // Forçar resize do Leaflet (necessário em modals)
        setTimeout(() => this.map.invalidateSize(), 200);
    },

    async calculateRoute() {
        const start = this.config.sedeCoords;
        const end = [parseFloat(document.getElementById('geo_lat').value), parseFloat(document.getElementById('geo_lng').value)];

        if (!start || !end[0]) return;

        try {
            // OSRM API (Gratuita)
            const resp = await fetch(`https://router.project-osrm.org/route/v1/driving/${start[1]},${start[0]};${end[1]},${end[0]}?overview=full&geometries=geojson`);
            const data = await resp.json();

            if (data.routes && data.routes.length) {
                const route = data.routes[0];

                // Desenha a linha da rota
                if (this.routeLayer) this.map.removeLayer(this.routeLayer);
                this.routeLayer = L.geoJSON(route.geometry, {
                    style: { color: '#2563eb', weight: 5, opacity: 0.7 }
                }).addTo(this.map);

                // Ajusta o zoom para ver os dois pontos
                const bounds = L.latLngBounds([start, end]);
                this.map.fitBounds(bounds, { padding: [50, 50] });

                // Atualiza painel de info
                const distKm = (route.distance / 1000).toFixed(1);
                const durationMin = Math.round(route.duration / 60);

                document.getElementById('route-distance').textContent = distKm + ' km';
                document.getElementById('route-duration').textContent = durationMin + ' min';

                // Cálculos de Estimativa
                const custoDeslocamento = distKm * this.config.custoKm * 2; // Ida e volta
                const estPedagio = distKm > 30 ? (distKm * this.config.custoPedagioKm) : 0;

                document.getElementById('est-pedagios').textContent = SGTUtils.formatMoney(estPedagio);
                document.getElementById('est-deslocamento').textContent = SGTUtils.formatMoney(custoDeslocamento);

                // Salva nos hiddens
                document.getElementById('geo_distancia').value = distKm;
                document.getElementById('geo_tempo').value = durationMin;
            }
        } catch (err) {
            console.error('Erro cálculo rota:', err);
        }
    },

    async reverseGeocode(lat, lng) {
        try {
            const resp = await fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}`);
            const data = await resp.json();
            if (data.display_name) {
                const addr = data.address;
                document.getElementById('endereco_obra').value = data.display_name;
                document.getElementById('endereco_rua').value = (addr.road || addr.pedestrian || addr.suburb || '');
                document.getElementById('bairro').value = addr.neighbourhood || addr.suburb || '';
                document.getElementById('cidade').value = addr.city || addr.town || addr.village || '';
            }
        } catch (err) {
            console.error('Erro reverse geocode:', err);
        }
    },

    applyToProposal() {
        const dist = document.getElementById('geo_distancia').value;
        const totalIdaVolta = (parseFloat(dist) * 2).toFixed(1);

        SGTUtils.showToast(`Distância de ${totalIdaVolta}km (ida/volta) registrada!`, 'success');
        this.closeModal();

        // Se quisermos injetar automaticamente nos custos, poderíamos buscar o input de deslocamento aqui
        // Mas por enquanto vamos apenas deixar salvo no formulário para o backend processar ou o usuário ver
    }
};

// Inicializa quando o documento estiver pronto
document.addEventListener('DOMContentLoaded', () => MapIntegration.init());
window.MapIntegration = MapIntegration;
