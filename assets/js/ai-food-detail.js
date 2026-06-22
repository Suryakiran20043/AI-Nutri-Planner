let aiMacroChartInstance = null;

async function openAIFoodModal(foodId, foodName = "Personalized Recipe") {
    // Dynamically inject the modal if it doesn't exist to guarantee it works on ALL pages
    let modal = document.getElementById('global-ai-food-modal');
    if (!modal) {
        modal = document.createElement('div');
        modal.id = 'global-ai-food-modal';
        // Inline CSS to guarantee styles regardless of components.css
        modal.style.position = 'fixed';
        modal.style.top = '0';
        modal.style.left = '0';
        modal.style.width = '100vw';
        modal.style.height = '100vh';
        modal.style.backgroundColor = 'rgba(17, 24, 39, 0.7)';
        modal.style.backdropFilter = 'blur(8px)';
        modal.style.zIndex = '999999'; // Guarantee it's on top of everything
        modal.style.display = 'flex';
        modal.style.alignItems = 'center';
        modal.style.justifyContent = 'center';
        modal.style.opacity = '0';
        modal.style.transition = 'opacity 0.3s ease';
        modal.style.pointerEvents = 'none';

        modal.innerHTML = `
            <div style="background: var(--surface, #FFFFFF); width: 95%; max-width: 900px; max-height: 90vh; border-radius: 16px; display: flex; flex-direction: column; overflow: hidden; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25);">
                <div style="padding: 20px 24px; border-bottom: 1px solid var(--border, #E5E7EB); display: flex; justify-content: space-between; align-items: center; background: var(--surface, #FFFFFF);">
                    <h3 id="global-ai-modal-title" style="margin: 0; font-family: 'Outfit', sans-serif; font-size: 20px; color: var(--text-primary, #111827);"></h3>
                    <button onclick="closeGlobalAIModal()" style="background: none; border: none; font-size: 24px; cursor: pointer; color: var(--text-secondary, #4B5563); padding: 4px;">&times;</button>
                </div>
                <div id="global-ai-modal-body" style="padding: 0; overflow-y: auto; flex: 1; background: var(--bg-body, #F9FAFB);"></div>
            </div>
        `;
        document.body.appendChild(modal);
    }

    const title = document.getElementById('global-ai-modal-title');
    const body = document.getElementById('global-ai-modal-body');

    title.innerText = foodName;
    body.innerHTML = `
        <div style="padding: 60px 40px; text-align: center; color: var(--text-secondary, #4B5563);">
            <div style="font-size: 40px; margin-bottom: 20px; animation: pulse 1.5s infinite;">🤖</div>
            <div style="font-size: 18px; font-weight: 500;">Consulting your Health Profile...</div>
            <div style="font-size: 14px; margin-top: 8px;">Generating Personalized Recipe</div>
        </div>
    `;
    
    // Trigger open
    requestAnimationFrame(() => {
        modal.style.opacity = '1';
        modal.style.pointerEvents = 'auto';
    });

    try {
        const response = await fetch('../api/personalize-recipe.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({ recipe_id: foodId, recipe_name: foodName })
        });
        const data = await response.json();
        
        if (data.status !== 'success') {
            throw new Error(data.message || 'Failed to personalize recipe.');
        }

        renderGlobalAIFoodUI(data);

    } catch (e) {
        body.innerHTML = `
            <div style="padding: 40px; text-align: center; color: #EF4444;">
                <div style="font-size: 24px; margin-bottom: 12px;">âš ï¸</div>
                <div style="font-weight: 600;">Failed to load AI details.</div>
                <div style="font-size: 13px; margin-top: 8px;">${e.message}</div>
            </div>
        `;
    }
}

function closeGlobalAIModal() {
    const modal = document.getElementById('global-ai-food-modal');
    if (modal) {
        modal.style.opacity = '0';
        modal.style.pointerEvents = 'none';
    }
}

function renderGlobalAIFoodUI(data) {
    const body = document.getElementById('global-ai-modal-body');
    const m = data.macros;
    
    // Ingredients
    const ingList = data.ingredients.map(ing => {
        let name = '';
        let qty = '';
        if (typeof ing === 'string') {
            const match = ing.match(/^([\d\/\.\s\-]+(?:cup|cups|tbsp|tbsps|tsp|tsps|oz|lb|lbs|g|ml|slice|slices|scoop|scoops|bunch|bunches|block|blocks|strip|strips|medium|large|small)?\b\s*)(.*)$/i);
            if (match) {
                qty = match[1].trim();
                name = match[2].trim();
            } else {
                name = ing;
            }
        } else if (ing) {
            name = ing.name || ing.ingredient_name || ing.food_name || "Unknown Ingredient";
            const q = ing.quantity || ing.ingredient_amount || ing.amount || "";
            const u = ing.units || ing.unit || ing.ingredient_unit || "";
            qty = (q + ' ' + u).trim() || "N/A";
        } else {
            name = "Unknown Ingredient";
            qty = "N/A";
        }
        
        return `<div style="display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px dashed var(--border, #E5E7EB);">
            <span style="color: var(--text-primary, #111827); font-weight: 500;">✓ ${name}</span>
            ${qty && qty !== 'N/A' ? `<span style="color: var(--mint, #10B981); font-weight: 700;">${qty}</span>` : ''}
        </div>`;
    }).join('');

    // Timeline Steps
    const stepsList = data.cooking_steps.map(step => `
        <div style="display: flex; margin-bottom: 24px;">
            <div style="display: flex; flex-direction: column; align-items: center; margin-right: 16px;">
                <div style="background: var(--forest, #1B3D2F); color: #fff; font-size: 11px; font-weight: 700; padding: 6px 10px; border-radius: 20px; white-space: nowrap;">
                    [${step.duration_min} min]
                </div>
                <div style="width: 2px; flex: 1; background: var(--border, #E5E7EB); margin-top: 8px;"></div>
            </div>
            <div style="background: var(--surface, #FFFFFF); padding: 16px; border-radius: 12px; flex: 1; border-left: 4px solid var(--forest, #1B3D2F); box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                <div style="font-weight: 600; color: var(--text-primary, #111827); margin-bottom: 12px; font-size: 15px;">${step.action}</div>
                
                <div style="font-size: 13px; color: var(--text-secondary, #4B5563); margin-bottom: 8px; line-height: 1.4;">
                    <strong style="color: var(--text-primary, #111827);">Why:</strong> ${step.why}
                </div>
                <div style="font-size: 13px; color: #EF4444; margin-bottom: 8px; line-height: 1.4;">
                    <strong style="color: #B91C1C;">Avoid:</strong> ${step.mistake}
                </div>
                <div style="font-size: 13px; color: #047857; background: #D1FAE5; padding: 10px; border-radius: 8px; border-left: 3px solid #10B981; line-height: 1.4;">
                    <strong>ðŸ’¡ Health Tip:</strong> ${step.health_tip}
                </div>
            </div>
        </div>
    `).join('');

    // Personalization Block
    const pBlock = data.personalization_block;
    const pPoints = pBlock.points.map(pt => `
        <div style="display: flex; align-items: flex-start; gap: 8px; margin-bottom: 10px; font-size: 14px; color: rgba(255,255,255,0.9); line-height: 1.4;">
            <span style="color: #34D399; font-weight: 700;">âœ“</span> ${pt}
        </div>
    `).join('');

    body.innerHTML = `
        <div style="display: flex; flex-direction: column;">
            <div style="padding: 24px;">
                
                <!-- Personalization Block -->
                <div style="background: linear-gradient(135deg, var(--forest, #1B3D2F) 0%, #2A5A46 100%); color: white; padding: 24px; border-radius: 16px; margin-bottom: 32px; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1);">
                    <div style="font-family: 'Playfair Display', serif; font-size: 20px; font-weight: 600; margin-bottom: 16px; display: flex; align-items: center; gap: 10px;">
                        <span>🧠</span> ${pBlock.title}
                    </div>
                    ${pPoints}
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 32px; margin-bottom: 40px;">
                    <!-- Ingredients -->
                    <div style="background: var(--surface, #FFFFFF); padding: 24px; border: 1px solid var(--border, #E5E7EB); border-radius: 16px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);">
                        <h4 style="margin-top: 0; margin-bottom: 20px; font-size: 18px; color: var(--text-primary, #111827); border-bottom: 3px solid var(--forest, #1B3D2F); padding-bottom: 10px; display: inline-block;">Personalized Ingredients</h4>
                        ${ingList}
                    </div>

                    <!-- Macro Chart & Nutrition Breakdown -->
                    <div style="background: var(--surface, #FFFFFF); padding: 24px; border: 1px solid var(--border, #E5E7EB); border-radius: 16px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); display: flex; flex-direction: column; align-items: center;">
                        <h4 style="margin-top: 0; margin-bottom: 20px; font-size: 18px; color: var(--text-primary, #111827);">Macro Distribution</h4>
                        <div style="width: 220px; height: 220px; position: relative;">
                            <canvas id="globalAiMacroPieChart"></canvas>
                        </div>
                        
                        <div style="width: 100%; margin-top: 32px; padding-top: 20px; border-top: 1px solid var(--border, #E5E7EB); display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                            <div style="text-align: center; background: #F3F4F6; padding: 12px; border-radius: 10px;">
                                <div style="font-size: 11px; color: #6B7280; text-transform: uppercase; font-weight: 700; letter-spacing: 0.05em;">Calories</div>
                                <div style="font-size: 20px; font-weight: 700; color: #111827;">${m.calories} <span style="font-size:12px; font-weight: 500;">kcal</span></div>
                            </div>
                            <div style="text-align: center; background: #FEF2F2; padding: 12px; border-radius: 10px;">
                                <div style="font-size: 11px; color: #EF4444; text-transform: uppercase; font-weight: 700; letter-spacing: 0.05em;">Sodium</div>
                                <div style="font-size: 20px; font-weight: 700; color: #B91C1C;">${m.sodium_mg} <span style="font-size:12px; font-weight: 500;">mg</span></div>
                            </div>
                            <div style="text-align: center; background: #EFF6FF; padding: 12px; border-radius: 10px;">
                                <div style="font-size: 11px; color: #3B82F6; text-transform: uppercase; font-weight: 700; letter-spacing: 0.05em;">Potassium</div>
                                <div style="font-size: 20px; font-weight: 700; color: #1D4ED8;">${m.potassium_mg} <span style="font-size:12px; font-weight: 500;">mg</span></div>
                            </div>
                            <div style="text-align: center; background: #FFFBEB; padding: 12px; border-radius: 10px;">
                                <div style="font-size: 11px; color: #F59E0B; text-transform: uppercase; font-weight: 700; letter-spacing: 0.05em;">Sugar</div>
                                <div style="font-size: 20px; font-weight: 700; color: #B45309;">${m.sugar_g} <span style="font-size:12px; font-weight: 500;">g</span></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Timeline Preparation Guide -->
                <div style="background: transparent;">
                    <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 24px; border-bottom: 3px solid var(--forest, #1B3D2F); padding-bottom: 10px;">
                        <h4 style="margin: 0; font-size: 20px; color: var(--text-primary, #111827);">IQ-200 Cooking Guide</h4>
                        <div style="font-size: 14px; font-weight: 700; color: var(--mint, #10B981); background: #ECFDF5; padding: 6px 12px; border-radius: 8px;">Total Time: ${data.total_time_min} mins</div>
                    </div>
                    ${stepsList}
                </div>

            </div>
        </div>
    `;

    // Render Chart.js
    const ctx = document.getElementById('globalAiMacroPieChart');
    if (ctx) {
        if (aiMacroChartInstance) {
            aiMacroChartInstance.destroy();
        }
        
        // Use hardcoded fallbacks to guarantee correct rendering
        aiMacroChartInstance = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Protein', 'Carbs', 'Fat', 'Fiber'],
                datasets: [{
                    data: [m.protein, m.carbs, m.fat, m.fiber],
                    backgroundColor: ['#3B82F6', '#F59E0B', '#F43F5E', '#10B981'],
                    borderWidth: 0,
                    hoverOffset: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '72%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            color: '#111827',
                            font: { family: "'Outfit', sans-serif", size: 13, weight: 'bold' },
                            usePointStyle: true,
                            padding: 20
                        }
                    }
                }
            }
        });
    }
}
