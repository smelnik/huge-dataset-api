Деталі завдання:

# Huge Dataset API

## Description

You are required to develop a PHP application with a single API endpoint:

- `GET /process-huge-dataset` - Returns a JSON array of objects with at least 5 elements. Each object must contain at least two fields.

## Special Requirements for `/process-huge-dataset`

This endpoint has several critical implementation requirements:

1. **Simulated Long-Running Operation**  
   The handler *must* include a `sleep(10)` command to simulate a long-running process.

2. **Caching Strategy**
    - The response must be cached for **1 minute**.
    - Caching **must not** be performed on local disk. Use a shared external cache store such as **Redis** or **Memcached**.
    - If **no cache exists at all** (even stale), and a request is already **processing the cache update**, the server **must return a `202 Accepted`** status.
    - If a **stale cache exists**, and another request is already refreshing it, the endpoint **must return the stale cached value**, accompanied by the header:
      ```
      X-Cache-Status: STALE
      ```

3. **Concurrency Control**
    - Only **one concurrent process** should fetch fresh data and update the cache at any given time.
    - This ensures that heavy computation or database access is performed once even under concurrent load.

4. **Data Structure**
    - The response format must be a **JSON array**.
    - It must contain at least **5 objects**.
    - Each object must contain **at least 2 fields**.

5. **API Documentation**
    - The endpoint must include **OpenAPI annotations**.

## Requirements
- PHP **8.0+**
- Symfony **6.0+**
- The `/process-huge-dataset` handler **must include `sleep(10)`**
- **OpenAPI annotations** must be present for the endpoint

## Code Quality
The codebase **must** include:
- **Automated tests** covering both functional and caching behavior.
- Tests should simulate multiple concurrent requests and verify:
    - Only one request processes the update
    - Others receive either stale cache or `202 Accepted` correctly
    - Headers like `X-Cache-Status` are present where required

## Additional Recommendations
Although not mandatory, the following are highly recommended to improve usability and deployment:

- A production-ready `Dockerfile`
- A `docker-compose.yml` file to simplify local development with services like Redis

## Submission Guidelines
- Upload the completed task to **GitHub**
- The repository **must include full commit history**
- **Submissions with a single commit will be rejected** — demonstrate incremental progress through multiple commits

## Development environment with Docker

### Steps for work:

* Install program `make`
* Build and up all containers. App will be available on http://127.0.0.1:8000/.
```shell
make biuld && make up
```
* More commands see to `./Makefile`

## Tests
For test coverage of code please use PHPUnit.
```shell
make test
```

## OpenAPI Documentation
OpenAPI Doc will be available on http://127.0.0.1:8000/api/doc
