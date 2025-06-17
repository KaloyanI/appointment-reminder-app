# Authentication API Documentation

This document outlines the authentication endpoints available in the API.

## Endpoints

### Register
- **URL**: `/api/register`
- **Method**: `POST`
- **Request Body**:
  ```json
  {
    "name": "string",
    "email": "string",
    "password": "string",
    "password_confirmation": "string"
  }
  ```
- **Success Response**: `201 Created`
  ```json
  {
    "message": "User registered successfully",
    "user": {
      "id": "integer",
      "name": "string",
      "email": "string"
    },
    "token": "string"
  }
  ```

### Login
- **URL**: `/api/login`
- **Method**: `POST`
- **Request Body**:
  ```json
  {
    "email": "string",
    "password": "string"
  }
  ```
- **Success Response**: `200 OK`
  ```json
  {
    "message": "User logged in successfully",
    "user": {
      "id": "integer",
      "name": "string",
      "email": "string"
    },
    "token": "string"
  }
  ```

### Get User Profile
- **URL**: `/api/user`
- **Method**: `GET`
- **Headers**: `Authorization: Bearer {token}`
- **Success Response**: `200 OK`
  ```json
  {
    "user": {
      "id": "integer",
      "name": "string",
      "email": "string"
    }
  }
  ```

### Update Profile
- **URL**: `/api/profile`
- **Method**: `PUT`
- **Headers**: `Authorization: Bearer {token}`
- **Request Body**:
  ```json
  {
    "name": "string (optional)",
    "email": "string (optional)",
    "current_password": "string (required if changing password)",
    "new_password": "string (optional)",
    "new_password_confirmation": "string (required if new_password is provided)"
  }
  ```
- **Success Response**: `200 OK`
  ```json
  {
    "message": "Profile updated successfully",
    "user": {
      "id": "integer",
      "name": "string",
      "email": "string"
    }
  }
  ```

### Logout
- **URL**: `/api/logout`
- **Method**: `POST`
- **Headers**: `Authorization: Bearer {token}`
- **Success Response**: `200 OK`
  ```json
  {
    "message": "Successfully logged out"
  }
  ```

### Refresh Token
- **URL**: `/api/refresh`
- **Method**: `POST`
- **Headers**: `Authorization: Bearer {token}`
- **Success Response**: `200 OK`
  ```json
  {
    "message": "Token refreshed successfully",
    "token": "string"
  }
  ```

## Error Responses

All endpoints may return the following error responses:

- **400 Bad Request**: Invalid request parameters
- **401 Unauthorized**: Invalid or expired token
- **422 Unprocessable Entity**: Validation errors
  ```json
  {
    "errors": {
      "field": ["error message"]
    }
  }
  ```

## Authentication

All protected endpoints require a valid Bearer token in the Authorization header:
```
Authorization: Bearer {your-token}
```

## Rate Limiting

The API implements rate limiting to prevent abuse. Please handle 429 Too Many Requests responses appropriately. 