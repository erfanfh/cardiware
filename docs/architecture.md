# 📘 Architecture Documentation

## 1. Overview

### 1.1 Purpose of Document

This document describes the architectural structure, modules,
development guidelines, and conventions used in the Moddingway platform. It
ensures:

- Clear modular boundaries
- Consistent development patterns
- Scalable and maintainable structure
- Shared understanding among developers

### 1.2 Technology Stack

- PHP 8.4
- Laravel 12
- Livewire 3
- MySQL (or MariaDB)
- TailwindCSS
- Blade Components

## 2. Architectural Style

This project follows **Modular Monolith Architecture** using
**Domain-Based Structure** with the following layers:

1. **Domain Layer**
2. **Application Layer**
3. **UI Layer**
4. **Infrastructure Layer**

## 3. Project Structure

    app/
        Application/
            {Module}/
                Actions/
                Queries/
                DTOs/

        Domain/
            {Module}/
                Models/
                Policies/
                Enums/
                ValueObjects/

        Http/
            Controllers/
                Admin/{Module}/
                Front/Module}/
        
        Middleware/
            {Module}/

        Infrastructure/
            Notifications/
            Services/
            Support/
            Traits/

        Livewire/
            Admin/{Module}/
            Front/{Module}/
      
        Providers/

    resources/
        views/
            front/{module}/
            admin/{module}/
            components/
            livewire/
                admin/{module}
                front/{module}
        vendor/

    routes/
        admin/{module}.php
        front/{module}.php
        web.php
        console.php

## 4. Domains

### 4.1 Identity

### 4.2 Learning

### 4.3 Financial

## 5. Layer Responsibilities

### Domain Layer

Contains: - Models - Policies - Enums & Value Objects - Rules

### Application Layer

Contains: - Use Cases (Actions) - Queries - DTOs

### UI Layer

Contains: - Controllers - Livewire Components - Blade templates

### Infrastructure Layer

Contains: - External service integrations (SMS, Email, APIs) -
Adapters - Gateways - Low-level services (file storage, caching
wrappers)

## 6. Naming Conventions

- UseCases: Verb + Object + Action
- Queries: Get/Paginate + Resource + Query
- Models: Singular
- Blade: snake-case

## 7. Action Classes

All Action classes must follow this convention:

- Only one public method named `execute()` is allowed.
- Action classes must not have a constructor. All inputs should be passed to the execute() method.

------------------------------------------------------------------------

# End of Document
