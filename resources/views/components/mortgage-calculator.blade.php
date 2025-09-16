{{-- Mortgage Calculator Widget --}}
@props([
    'propertyPrice' => 0,
    'defaultDownPayment' => 20,
    'defaultInterestRate' => 6.5,
    'defaultLoanTerm' => 30
])

<div class="mortgage-calculator card shadow-sm">
    <div class="card-header bg-primary text-white">
        <h4 class="card-title mb-0">
            <i class="fi-calculator me-2"></i>Mortgage Calculator
        </h4>
    </div>
    
    <div class="card-body">
        <form id="mortgage-form">
            <!-- Home Price -->
            <div class="mb-4">
                <label for="home-price" class="form-label fw-medium">Home Price</label>
                <div class="input-group">
                    <span class="input-group-text">$</span>
                    <input type="number" class="form-control form-control-lg" id="home-price" 
                           value="{{ $propertyPrice }}" min="1000" step="1000">
                </div>
            </div>
            
            <!-- Down Payment -->
            <div class="mb-4">
                <label for="down-payment-percent" class="form-label fw-medium">
                    Down Payment 
                    <span class="text-muted">(<span id="down-payment-amount">$0</span>)</span>
                </label>
                <div class="row g-2">
                    <div class="col-8">
                        <div class="range-slider">
                            <input type="range" class="form-range" id="down-payment-percent" 
                                   min="0" max="50" step="1" value="{{ $defaultDownPayment }}">
                            <div class="d-flex justify-content-between text-muted small">
                                <span>0%</span>
                                <span>25%</span>
                                <span>50%</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="input-group">
                            <input type="number" class="form-control" id="down-payment-input" 
                                   value="{{ $defaultDownPayment }}" min="0" max="50" step="0.5">
                            <span class="input-group-text">%</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Interest Rate -->
            <div class="mb-4">
                <label for="interest-rate" class="form-label fw-medium">Interest Rate (APR)</label>
                <div class="input-group">
                    <input type="number" class="form-control" id="interest-rate" 
                           value="{{ $defaultInterestRate }}" min="0.1" max="15" step="0.01">
                    <span class="input-group-text">%</span>
                </div>
                <small class="form-text text-muted">Current average rate: ~6.5%</small>
            </div>
            
            <!-- Loan Term -->
            <div class="mb-4">
                <label for="loan-term" class="form-label fw-medium">Loan Term</label>
                <select class="form-select" id="loan-term">
                    <option value="15" {{ $defaultLoanTerm == 15 ? 'selected' : '' }}>15 years</option>
                    <option value="20" {{ $defaultLoanTerm == 20 ? 'selected' : '' }}>20 years</option>
                    <option value="25" {{ $defaultLoanTerm == 25 ? 'selected' : '' }}>25 years</option>
                    <option value="30" {{ $defaultLoanTerm == 30 ? 'selected' : '' }}>30 years</option>
                </select>
            </div>
            
            <!-- Additional Costs (Collapsible) -->
            <div class="accordion mb-4" id="additionalCosts">
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" 
                                data-bs-toggle="collapse" data-bs-target="#costsCollapse">
                            <i class="fi-settings me-2"></i>Additional Monthly Costs
                        </button>
                    </h2>
                    <div id="costsCollapse" class="accordion-collapse collapse">
                        <div class="accordion-body">
                            <div class="row g-3">
                                <div class="col-6">
                                    <label for="property-tax" class="form-label">Property Tax</label>
                                    <div class="input-group">
                                        <span class="input-group-text">$</span>
                                        <input type="number" class="form-control" id="property-tax" 
                                               value="0" min="0" step="50">
                                        <span class="input-group-text">/mo</span>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <label for="home-insurance" class="form-label">Home Insurance</label>
                                    <div class="input-group">
                                        <span class="input-group-text">$</span>
                                        <input type="number" class="form-control" id="home-insurance" 
                                               value="0" min="0" step="25">
                                        <span class="input-group-text">/mo</span>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <label for="pmi" class="form-label">PMI</label>
                                    <div class="input-group">
                                        <span class="input-group-text">$</span>
                                        <input type="number" class="form-control" id="pmi" 
                                               value="0" min="0" step="25">
                                        <span class="input-group-text">/mo</span>
                                    </div>
                                    <small class="form-text text-muted">Required if down payment &lt; 20%</small>
                                </div>
                                <div class="col-6">
                                    <label for="hoa-fees" class="form-label">HOA Fees</label>
                                    <div class="input-group">
                                        <span class="input-group-text">$</span>
                                        <input type="number" class="form-control" id="hoa-fees" 
                                               value="0" min="0" step="25">
                                        <span class="input-group-text">/mo</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
        
        <!-- Results -->
        <div class="mortgage-results">
            <!-- Monthly Payment Highlight -->
            <div class="alert alert-primary d-flex justify-content-between align-items-center mb-3">
                <div>
                    <strong class="fs-5">Monthly Payment</strong>
                    <div class="text-muted small">Principal & Interest</div>
                </div>
                <div class="text-end">
                    <div class="fs-3 fw-bold" id="monthly-payment">$0</div>
                </div>
            </div>
            
            <!-- Total Monthly Payment -->
            <div class="row g-3 mb-4">
                <div class="col-6">
                    <div class="d-flex justify-content-between">
                        <span>Principal & Interest:</span>
                        <strong id="monthly-pi">$0</strong>
                    </div>
                </div>
                <div class="col-6">
                    <div class="d-flex justify-content-between">
                        <span>Total Monthly:</span>
                        <strong id="total-monthly">$0</strong>
                    </div>
                </div>
            </div>
            
            <!-- Breakdown -->
            <div class="row g-3 text-center">
                <div class="col-4">
                    <div class="border rounded p-3">
                        <div class="fs-4 fw-bold text-primary" id="loan-amount">$0</div>
                        <small class="text-muted">Loan Amount</small>
                    </div>
                </div>
                <div class="col-4">
                    <div class="border rounded p-3">
                        <div class="fs-4 fw-bold text-success" id="total-interest">$0</div>
                        <small class="text-muted">Total Interest</small>
                    </div>
                </div>
                <div class="col-4">
                    <div class="border rounded p-3">
                        <div class="fs-4 fw-bold text-info" id="total-cost">$0</div>
                        <small class="text-muted">Total Cost</small>
                    </div>
                </div>
            </div>
            
            <!-- Affordability Indicator -->
            <div class="mt-4">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <small class="text-muted">Affordability Guide</small>
                    <small class="text-muted">Based on 28% debt-to-income ratio</small>
                </div>
                <div class="progress" style="height: 8px;">
                    <div class="progress-bar bg-success" id="affordability-bar" style="width: 0%"></div>
                </div>
                <div class="d-flex justify-content-between mt-1">
                    <small class="text-success">Affordable</small>
                    <small class="text-warning">Moderate</small>
                    <small class="text-danger">High Risk</small>
                </div>
                <div class="text-center mt-2">
                    <small class="text-muted">
                        Recommended monthly income: <strong id="recommended-income">$0</strong>
                    </small>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Action Buttons -->
    <div class="card-footer bg-light">
        <div class="row g-2">
            <div class="col-6">
                <button type="button" class="btn btn-outline-primary w-100" id="reset-calculator">
                    <i class="fi-refresh-cw me-1"></i>Reset
                </button>
            </div>
            <div class="col-6">
                <button type="button" class="btn btn-primary w-100" id="save-calculation">
                    <i class="fi-bookmark me-1"></i>Save
                </button>
            </div>
        </div>
        <div class="text-center mt-2">
            <small class="text-muted">
                <i class="fi-info me-1"></i>
                Estimates are for informational purposes only. Consult a lender for accurate rates.
            </small>
        </div>
    </div>
</div>

<style>
.mortgage-calculator {
    max-width: 500px;
}

.mortgage-calculator .form-range {
    height: 6px;
}

.mortgage-calculator .form-range::-webkit-slider-thumb {
    height: 20px;
    width: 20px;
    background: var(--bs-primary);
}

.mortgage-calculator .form-range::-moz-range-thumb {
    height: 20px;
    width: 20px;
    background: var(--bs-primary);
    border: none;
}

.mortgage-calculator .progress-bar {
    transition: width 0.3s ease, background-color 0.3s ease;
}

.mortgage-calculator .alert-primary {
    background: linear-gradient(135deg, var(--bs-primary), var(--bs-primary-dark));
    border: none;
}

@media (max-width: 576px) {
    .mortgage-calculator .row g-3 .col-4 {
        margin-bottom: 1rem;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const calculator = new MortgageCalculator();
});

class MortgageCalculator {
    constructor() {
        this.elements = {
            homePrice: document.getElementById('home-price'),
            downPaymentPercent: document.getElementById('down-payment-percent'),
            downPaymentInput: document.getElementById('down-payment-input'),
            downPaymentAmount: document.getElementById('down-payment-amount'),
            interestRate: document.getElementById('interest-rate'),
            loanTerm: document.getElementById('loan-term'),
            propertyTax: document.getElementById('property-tax'),
            homeInsurance: document.getElementById('home-insurance'),
            pmi: document.getElementById('pmi'),
            hoaFees: document.getElementById('hoa-fees'),
            monthlyPayment: document.getElementById('monthly-payment'),
            monthlyPI: document.getElementById('monthly-pi'),
            totalMonthly: document.getElementById('total-monthly'),
            loanAmount: document.getElementById('loan-amount'),
            totalInterest: document.getElementById('total-interest'),
            totalCost: document.getElementById('total-cost'),
            affordabilityBar: document.getElementById('affordability-bar'),
            recommendedIncome: document.getElementById('recommended-income'),
            resetButton: document.getElementById('reset-calculator'),
            saveButton: document.getElementById('save-calculation')
        };
        
        this.init();
    }
    
    init() {
        this.setupEventListeners();
        this.calculate();
        this.updatePMI();
    }
    
    setupEventListeners() {
        // Input event listeners
        Object.values(this.elements).forEach(element => {
            if (element && element.tagName !== 'DIV' && element.tagName !== 'BUTTON') {
                element.addEventListener('input', () => {
                    this.calculate();
                    this.updatePMI();
                });
            }
        });
        
        // Sync down payment slider and input
        this.elements.downPaymentPercent.addEventListener('input', (e) => {
            this.elements.downPaymentInput.value = e.target.value;
            this.calculate();
            this.updatePMI();
        });
        
        this.elements.downPaymentInput.addEventListener('input', (e) => {
            this.elements.downPaymentPercent.value = e.target.value;
            this.calculate();
            this.updatePMI();
        });
        
        // Reset button
        this.elements.resetButton.addEventListener('click', () => {
            this.reset();
        });
        
        // Save button
        this.elements.saveButton.addEventListener('click', () => {
            this.saveCalculation();
        });
    }
    
    calculate() {
        const homePrice = parseFloat(this.elements.homePrice.value) || 0;
        const downPaymentPercent = parseFloat(this.elements.downPaymentPercent.value) || 0;
        const interestRate = parseFloat(this.elements.interestRate.value) || 0;
        const loanTermYears = parseInt(this.elements.loanTerm.value) || 30;
        
        // Additional costs
        const propertyTax = parseFloat(this.elements.propertyTax.value) || 0;
        const homeInsurance = parseFloat(this.elements.homeInsurance.value) || 0;
        const pmi = parseFloat(this.elements.pmi.value) || 0;
        const hoaFees = parseFloat(this.elements.hoaFees.value) || 0;
        
        // Calculations
        const downPaymentAmount = homePrice * (downPaymentPercent / 100);
        const loanAmount = homePrice - downPaymentAmount;
        const monthlyRate = interestRate / 100 / 12;
        const numberOfPayments = loanTermYears * 12;
        
        // Monthly payment calculation (P&I only)
        let monthlyPayment = 0;
        if (monthlyRate > 0 && numberOfPayments > 0) {
            monthlyPayment = loanAmount * (monthlyRate * Math.pow(1 + monthlyRate, numberOfPayments)) / 
                           (Math.pow(1 + monthlyRate, numberOfPayments) - 1);
        } else {
            monthlyPayment = loanAmount / numberOfPayments;
        }
        
        const totalInterest = (monthlyPayment * numberOfPayments) - loanAmount;
        const totalCost = homePrice + totalInterest;
        const totalMonthlyPayment = monthlyPayment + propertyTax + homeInsurance + pmi + hoaFees;
        const recommendedIncome = totalMonthlyPayment / 0.28; // 28% rule
        
        // Update display
        this.elements.downPaymentAmount.textContent = this.formatCurrency(downPaymentAmount);
        this.elements.monthlyPayment.textContent = this.formatCurrency(monthlyPayment);
        this.elements.monthlyPI.textContent = this.formatCurrency(monthlyPayment);
        this.elements.totalMonthly.textContent = this.formatCurrency(totalMonthlyPayment);
        this.elements.loanAmount.textContent = this.formatCurrency(loanAmount);
        this.elements.totalInterest.textContent = this.formatCurrency(totalInterest);
        this.elements.totalCost.textContent = this.formatCurrency(totalCost);
        this.elements.recommendedIncome.textContent = this.formatCurrency(recommendedIncome);
        
        // Update affordability indicator
        this.updateAffordabilityIndicator(totalMonthlyPayment, recommendedIncome);
    }
    
    updatePMI() {
        const downPaymentPercent = parseFloat(this.elements.downPaymentPercent.value) || 0;
        const homePrice = parseFloat(this.elements.homePrice.value) || 0;
        
        if (downPaymentPercent < 20 && homePrice > 0) {
            const estimatedPMI = (homePrice * 0.005) / 12; // 0.5% annually
            this.elements.pmi.value = Math.round(estimatedPMI);
            this.elements.pmi.parentElement.parentElement.classList.add('border-warning');
        } else {
            this.elements.pmi.value = 0;
            this.elements.pmi.parentElement.parentElement.classList.remove('border-warning');
        }
    }
    
    updateAffordabilityIndicator(monthlyPayment, recommendedIncome) {
        const ratio = monthlyPayment / (recommendedIncome * 0.28);
        const percentage = Math.min(ratio * 100, 100);
        
        this.elements.affordabilityBar.style.width = percentage + '%';
        
        if (percentage <= 100) {
            this.elements.affordabilityBar.className = 'progress-bar bg-success';
        } else if (percentage <= 130) {
            this.elements.affordabilityBar.className = 'progress-bar bg-warning';
        } else {
            this.elements.affordabilityBar.className = 'progress-bar bg-danger';
        }
    }
    
    formatCurrency(amount) {
        return new Intl.NumberFormat('en-US', {
            style: 'currency',
            currency: 'USD',
            minimumFractionDigits: 0,
            maximumFractionDigits: 0
        }).format(amount);
    }
    
    reset() {
        this.elements.homePrice.value = {{ $propertyPrice }};
        this.elements.downPaymentPercent.value = {{ $defaultDownPayment }};
        this.elements.downPaymentInput.value = {{ $defaultDownPayment }};
        this.elements.interestRate.value = {{ $defaultInterestRate }};
        this.elements.loanTerm.value = {{ $defaultLoanTerm }};
        this.elements.propertyTax.value = 0;
        this.elements.homeInsurance.value = 0;
        this.elements.pmi.value = 0;
        this.elements.hoaFees.value = 0;
        
        this.calculate();
        this.updatePMI();
    }
    
    saveCalculation() {
        const calculation = {
            homePrice: this.elements.homePrice.value,
            downPayment: this.elements.downPaymentPercent.value,
            interestRate: this.elements.interestRate.value,
            loanTerm: this.elements.loanTerm.value,
            monthlyPayment: this.elements.monthlyPayment.textContent,
            timestamp: new Date().toISOString()
        };
        
        // Save to localStorage
        let savedCalculations = JSON.parse(localStorage.getItem('mortgageCalculations') || '[]');
        savedCalculations.unshift(calculation);
        savedCalculations = savedCalculations.slice(0, 10); // Keep only last 10
        localStorage.setItem('mortgageCalculations', JSON.stringify(savedCalculations));
        
        // Show feedback
        const originalText = this.elements.saveButton.innerHTML;
        this.elements.saveButton.innerHTML = '<i class="fi-check me-1"></i>Saved!';
        this.elements.saveButton.classList.remove('btn-primary');
        this.elements.saveButton.classList.add('btn-success');
        
        setTimeout(() => {
            this.elements.saveButton.innerHTML = originalText;
            this.elements.saveButton.classList.remove('btn-success');
            this.elements.saveButton.classList.add('btn-primary');
        }, 2000);
    }
}
</script>