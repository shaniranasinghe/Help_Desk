<?php
class CompanyModel {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    // Retrieve all companies
    public function fetchAllCompanies() {
        $query = "SELECT * FROM companies ORDER BY date_created DESC";
        $result = $this->conn->query($query);
        return $result;
    }

    // Insert a new company
    public function insertCompany($company_name, $company_email, $company_type) {
        $query = "INSERT INTO companies (company_name, company_email, company_type, date_created) 
                  VALUES (?, ?, ?, NOW())";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("sss", $company_name, $company_email, $company_type);
        return $stmt->execute();
    }

    // Get a company by its ID
    public function getCompanyById($company_id) {
        $query = "SELECT * FROM companies WHERE company_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $company_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    // Delete a company by its ID
    public function deleteCompany($company_id) {
        $query = "DELETE FROM companies WHERE company_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $company_id);
        return $stmt->execute();
    }

    // Update a company by its ID
    public function updateCompany($company_id, $company_name, $company_email, $company_type) {
        $query = "UPDATE companies SET company_name = ?, company_email = ?, company_type = ? WHERE company_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("sssi", $company_name, $company_email, $company_type, $company_id);
        return $stmt->execute();
    }
    
}
?>
