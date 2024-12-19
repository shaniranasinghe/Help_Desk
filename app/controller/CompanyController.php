<?php
include_once '../../model/CompanyModel.php';

class CompanyController {
    private $companyModel;

    public function __construct($conn) {
        $this->companyModel = new CompanyModel($conn);
    }

    // Retrieve all companies
    public function getAllCompanies() {
        return $this->companyModel->fetchAllCompanies();
    }

    // Add a new company
    public function addCompany($company_name, $company_email, $company_type) {
        return $this->companyModel->insertCompany($company_name, $company_email, $company_type);
    }

    // Edit an existing company
    public function editCompany($company_id, $company_name, $company_email, $company_type) {
        return $this->companyModel->updateCompany($company_id, $company_name, $company_email, $company_type);
    }

    // Delete a company
    public function deleteCompany($company_id) {
        return $this->companyModel->deleteCompany($company_id);
    }

    // Get company details by ID
    public function getCompany($company_id) {
        return $this->companyModel->getCompanyById($company_id);
    }
}
?>
